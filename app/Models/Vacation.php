<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use App\Enums\VacationPhase;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacation extends Model
{
    protected $fillable = [
        'name',
        'description',
        'phase',
        'planning_start_date',
        'planning_end_date',
        'final_start_date',
        'final_end_date',
    ];

    protected function casts(): array
    {
        return [
            'phase' => VacationPhase::class,
            'planning_start_date' => 'date:Y-m-d',
            'planning_end_date' => 'date:Y-m-d',
            'final_start_date' => 'date:Y-m-d',
            'final_end_date' => 'date:Y-m-d',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vacation_user');
    }

    public function dateAvailabilities(): HasMany
    {
        return $this->hasMany(VacationDateAvailability::class);
    }

    public function skiAreas(): HasMany
    {
        return $this->hasMany(VacationSkiArea::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(VacationVehicle::class);
    }

    public function travelOptions(): HasMany
    {
        return $this->hasMany(VacationTravelOption::class);
    }

    public function packingItems(): HasMany
    {
        return $this->hasMany(VacationPackingItem::class);
    }

    /**
     * All dates (as 'Y-m-d' strings) in the candidate range being polled.
     *
     * @return array<int, string>
     */
    public function planningDates(): array
    {
        if (! $this->planning_start_date || ! $this->planning_end_date) {
            return [];
        }

        return collect(CarbonPeriod::create($this->planning_start_date, $this->planning_end_date))
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->all();
    }

    /**
     * Ranked suggestions for the final date range: contiguous streaks of dates
     * scored by (people who can attend every day in the streak) x (streak length),
     * keeping only "maximal" streaks (ones that can't be extended without losing
     * someone), so a solid block like "everyone can 15-20 aug" surfaces instead of
     * a pile of overlapping sub-ranges.
     *
     * @return array<int, array{start: string, end: string, count: int, total: int, length: int}>
     */
    public function topDateRangeSuggestions(int $limit = 3): array
    {
        $dates = $this->planningDates();
        $totalParticipants = $this->users->count();

        if (empty($dates) || $totalParticipants === 0) {
            return [];
        }

        $canByDate = $this->dateAvailabilities
            ->filter(fn ($availability) => $availability->status === AvailabilityStatus::Can)
            ->groupBy(fn ($availability) => $availability->date->format('Y-m-d'))
            ->map(fn ($items) => $items->pluck('user_id')->all());

        $n = count($dates);
        $candidates = [];

        for ($i = 0; $i < $n; $i++) {
            $available = collect($canByDate->get($dates[$i], []));

            if ($available->isEmpty()) {
                continue;
            }

            for ($j = $i; $j < $n; $j++) {
                $available = $available->intersect($canByDate->get($dates[$j], []));

                if ($available->isEmpty()) {
                    break;
                }

                $candidates[] = [
                    'start' => $dates[$i],
                    'end' => $dates[$j],
                    'count' => $available->count(),
                ];
            }
        }

        $maximal = [];

        foreach ($candidates as $index => $candidate) {
            $isDominated = false;

            foreach ($candidates as $otherIndex => $other) {
                if ($otherIndex === $index) {
                    continue;
                }

                $contains = $other['start'] <= $candidate['start'] && $other['end'] >= $candidate['end'];
                $strictlyLarger = $contains && ($other['start'] < $candidate['start'] || $other['end'] > $candidate['end']);

                if ($strictlyLarger && $other['count'] >= $candidate['count']) {
                    $isDominated = true;
                    break;
                }
            }

            if (! $isDominated) {
                $maximal[] = $candidate;
            }
        }

        $maximal = collect($maximal)->unique(fn ($c) => $c['start'].'|'.$c['end'])->all();

        // Most people able to attend wins first; among equal counts, the longer streak wins.
        // (A 3-day streak everyone can make beats an 11-day streak only half the group can.)
        usort($maximal, function ($a, $b) {
            if ($a['count'] !== $b['count']) {
                return $b['count'] <=> $a['count'];
            }

            $lengthOf = fn ($c) => strtotime($c['end']) - strtotime($c['start']);

            return $lengthOf($b) <=> $lengthOf($a);
        });

        return collect($maximal)
            ->take($limit)
            ->map(function ($candidate) use ($totalParticipants) {
                $length = (strtotime($candidate['end']) - strtotime($candidate['start'])) / 86400 + 1;

                return [
                    'start' => $candidate['start'],
                    'end' => $candidate['end'],
                    'count' => $candidate['count'],
                    'total' => $totalParticipants,
                    'length' => (int) $length,
                ];
            })
            ->all();
    }
}
