<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use App\Enums\VacationPhase;
use Carbon\CarbonPeriod;
use Database\Factories\VacationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Vacation extends Model
{
    /** @use HasFactory<VacationFactory> */
    use HasFactory;

    /**
     * Standaard ondergrens voor de duur van de vakantie bij het zoeken naar datums.
     */
    public const MINIMUM_TRIP_DAYS = 5;

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
     * Alle "maximale" aaneengesloten reeksen waarop een groep tegelijk kan: reeksen
     * die niet verlengd kunnen worden zonder iemand kwijt te raken. Zo blijft er van
     * een blok als "iedereen kan 15-20 aug" een kandidaat over in plaats van een stapel
     * overlappende deelreeksen. Gesorteerd op meeste mensen, daarna langste reeks.
     *
     * Met $includeMaybe telt "misschien" mee als meekunnen; los daarvan krijgt elke
     * kandidaat altijd de id's mee van wie de hele reeks zeker kan ('can'), wie er
     * misschien bij is ('maybe'), wie minstens een dag niet kan ('cannot') en wie nog
     * niet alles heeft ingevuld ('unknown'). Alle vier op naam gesorteerd.
     *
     * @return array<int, array{start: string, end: string, count: int, total: int, length: int, can: array<int, int>, maybe: array<int, int>, cannot: array<int, int>, unknown: array<int, int>}>
     */
    public function dateRangeCandidates(bool $includeMaybe = false): array
    {
        $dates = $this->planningDates();
        $participants = $this->users;
        $totalParticipants = $participants->count();

        if (empty($dates) || $totalParticipants === 0) {
            return [];
        }

        $names = $participants->pluck('name', 'id');

        $usersPerDate = fn (array $statuses) => $this->dateAvailabilities
            ->filter(fn ($availability) => in_array($availability->status, $statuses, true))
            ->groupBy(fn ($availability) => $availability->date->format('Y-m-d'))
            ->map(fn ($items) => $items->pluck('user_id')->all());

        $canByDate = $usersPerDate([AvailabilityStatus::Can]);
        $canOrMaybeByDate = $usersPerDate([AvailabilityStatus::Can, AvailabilityStatus::Maybe]);
        $cannotByDate = $usersPerDate([AvailabilityStatus::Cannot]);
        $countingByDate = $includeMaybe ? $canOrMaybeByDate : $canByDate;

        $n = count($dates);

        // countPerRange[i][j] = hoeveel mensen de hele reeks dag i t/m dag j kunnen.
        $countPerRange = [];

        for ($i = 0; $i < $n; $i++) {
            $available = collect($countingByDate->get($dates[$i], []));

            if ($available->isEmpty()) {
                continue;
            }

            for ($j = $i; $j < $n; $j++) {
                $available = $available->intersect($countingByDate->get($dates[$j], []));

                if ($available->isEmpty()) {
                    break;
                }

                $countPerRange[$i][$j] = $available->count();
            }
        }

        // Een reeks is "maximaal" als geen enkele grotere reeks er evenveel mensen bij
        // houdt. Omdat het aantal mensen alleen maar kan dalen als je een reeks oprekt,
        // volstaat het om één dag naar links en één dag naar rechts te kijken: bestaat er
        // een grotere reeks met minstens evenveel mensen, dan heeft die tussenstap dat ook.
        $maximal = [];

        foreach ($countPerRange as $i => $countPerEnd) {
            foreach ($countPerEnd as $j => $count) {
                $oneDayEarlier = $countPerRange[$i - 1][$j] ?? null;
                $oneDayLater = $countPerRange[$i][$j + 1] ?? null;

                if ($oneDayEarlier >= $count || $oneDayLater >= $count) {
                    continue;
                }

                $maximal[] = [
                    'start' => $dates[$i],
                    'end' => $dates[$j],
                    'count' => $count,
                ];
            }
        }

        // Wie de hele reeks door beschikbaar is, per reeks: groen op elke dag, of
        // groen/oranje op elke dag (en dus "misschien" voor de reeks als geheel).
        $whoIsFree = function (Collection $byDate, string $start, string $end) use ($dates): array {
            $ids = null;

            foreach ($dates as $date) {
                if ($date < $start || $date > $end) {
                    continue;
                }

                $onThisDay = $byDate->get($date, []);
                $ids = $ids === null ? $onThisDay : array_intersect($ids, $onThisDay);

                if (empty($ids)) {
                    return [];
                }
            }

            return array_values($ids ?? []);
        };

        // En wie de reeks juist blokkeert: een enkele rode dag is genoeg.
        $whoIsBlocked = function (Collection $byDate, string $start, string $end) use ($dates): array {
            $ids = [];

            foreach ($dates as $date) {
                if ($date < $start || $date > $end) {
                    continue;
                }

                $ids = array_merge($ids, $byDate->get($date, []));
            }

            return array_values(array_unique($ids));
        };

        return collect($maximal)
            ->map(function ($candidate) use ($totalParticipants, $names, $canByDate, $canOrMaybeByDate, $cannotByDate, $whoIsFree, $whoIsBlocked) {
                $length = (strtotime($candidate['end']) - strtotime($candidate['start'])) / 86400 + 1;
                $canIds = $whoIsFree($canByDate, $candidate['start'], $candidate['end']);
                $freeIds = $whoIsFree($canOrMaybeByDate, $candidate['start'], $candidate['end']);
                $maybeIds = array_diff($freeIds, $canIds);
                $cannotIds = $whoIsBlocked($cannotByDate, $candidate['start'], $candidate['end']);
                // Wie niet meekan en ook niet geblokkeerd is, heeft simpelweg nog niet
                // elke dag van deze reeks ingevuld.
                $unknownIds = array_diff($names->keys()->all(), $freeIds, $cannotIds);

                $byName = fn (array $ids) => collect($ids)
                    ->filter(fn ($id) => $names->has($id))
                    ->sortBy(fn ($id) => $names->get($id))
                    ->values()
                    ->all();

                return [
                    'start' => $candidate['start'],
                    'end' => $candidate['end'],
                    'count' => $candidate['count'],
                    'total' => $totalParticipants,
                    'length' => (int) $length,
                    'can' => $byName($canIds),
                    'maybe' => $byName($maybeIds),
                    'cannot' => $byName($cannotIds),
                    'unknown' => $byName($unknownIds),
                ];
            })
            // Meeste mensen wint; bij gelijk aantal wint de langere reeks.
            ->sortBy([
                ['count', 'desc'],
                ['length', 'desc'],
            ])
            ->values()
            ->all();
    }

    /**
     * Deelnemers die nog in geen enkele auto zijn ingedeeld, zodat de reisplanner
     * kan laten zien wie er nog geen plek heeft.
     *
     * @return Collection<int, User>
     */
    public function participantsWithoutVehicle(): Collection
    {
        $seated = $this->vehicles
            ->flatMap(fn ($vehicle) => $vehicle->passengers->pluck('id'))
            ->unique();

        return $this->users->reject(fn ($user) => $seated->contains($user->id))->values();
    }

    /**
     * Hoeveel dagen van het gepeilde bereik iemand al heeft ingevuld. Voedt de
     * "nog X dagen te gaan"-hint op het dashboard.
     *
     * @return array{filled: int, total: int}
     */
    public function availabilityProgressFor(User $user): array
    {
        $dates = $this->planningDates();

        if (empty($dates)) {
            return ['filled' => 0, 'total' => 0];
        }

        $filled = $this->dateAvailabilities
            ->where('user_id', $user->id)
            ->filter(fn ($availability) => in_array($availability->date->format('Y-m-d'), $dates, true))
            ->count();

        return ['filled' => $filled, 'total' => count($dates)];
    }

    /**
     * Ranked suggesties voor de definitieve datum, met een ondergrens voor de duur:
     * een vakantie van minder dan een paar dagen heeft geen zin, dus liever een reeks
     * van {@see self::MINIMUM_TRIP_DAYS} dagen met minder mensen dan een lang weekend
     * waar iedereen kan. Haalt geen enkele reeks de ondergrens, dan vallen we terug op
     * de langste reeksen die er wél zijn.
     *
     * @return array<int, array{start: string, end: string, count: int, total: int, length: int}>
     */
    public function topDateRangeSuggestions(int $limit = 3, ?int $minimumDays = null, bool $includeMaybe = false): array
    {
        $candidates = collect($this->dateRangeCandidates($includeMaybe));
        $matching = $candidates->filter(
            fn ($candidate) => $candidate['length'] >= ($minimumDays ?? self::MINIMUM_TRIP_DAYS)
        );

        if ($matching->isEmpty()) {
            $matching = $candidates->sortByDesc('length');
        }

        return $matching->take($limit)->values()->all();
    }
}
