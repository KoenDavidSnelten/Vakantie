<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Models\User;
use App\Models\Vacation;
use App\Models\VacationDateAvailability;
use Illuminate\Database\Seeder;

class VacationParticipantsSeeder extends Seeder
{
    /**
     * Demo participants, each with their own answering habits so the date
     * planner shows a realistic spread instead of five identical calendars.
     *
     * @var array<int, array{name: string, email: string, weights: array<string, int>}>
     */
    protected array $participants = [
        ['name' => 'Sanne de Vries', 'email' => 'sanne@example.com', 'weights' => ['can' => 70, 'maybe' => 20, 'cannot' => 10]],
        ['name' => 'Thijs Bakker', 'email' => 'thijs@example.com', 'weights' => ['can' => 45, 'maybe' => 25, 'cannot' => 30]],
        ['name' => 'Lotte Jansen', 'email' => 'lotte@example.com', 'weights' => ['can' => 55, 'maybe' => 30, 'cannot' => 15]],
        ['name' => 'Daan Visser', 'email' => 'daan@example.com', 'weights' => ['can' => 35, 'maybe' => 20, 'cannot' => 45]],
        ['name' => 'Fleur Mulder', 'email' => 'fleur@example.com', 'weights' => ['can' => 60, 'maybe' => 15, 'cannot' => 25]],
        ['name' => 'Bram de Jong', 'email' => 'bram@example.com', 'weights' => ['can' => 65, 'maybe' => 20, 'cannot' => 15]],
        ['name' => 'Emma Willems', 'email' => 'emma@example.com', 'weights' => ['can' => 40, 'maybe' => 35, 'cannot' => 25]],
        ['name' => 'Jesse van Dijk', 'email' => 'jesse@example.com', 'weights' => ['can' => 50, 'maybe' => 20, 'cannot' => 30]],
        ['name' => 'Noa Peters', 'email' => 'noa@example.com', 'weights' => ['can' => 75, 'maybe' => 15, 'cannot' => 10]],
        ['name' => 'Ruben Smit', 'email' => 'ruben@example.com', 'weights' => ['can' => 30, 'maybe' => 30, 'cannot' => 40]],
        ['name' => 'Julia Hoekstra', 'email' => 'julia@example.com', 'weights' => ['can' => 55, 'maybe' => 25, 'cannot' => 20]],
        ['name' => 'Milan Kuipers', 'email' => 'milan@example.com', 'weights' => ['can' => 45, 'maybe' => 30, 'cannot' => 25]],
        ['name' => 'Anouk Vermeulen', 'email' => 'anouk@example.com', 'weights' => ['can' => 60, 'maybe' => 25, 'cannot' => 15]],
        ['name' => 'Sem Timmermans', 'email' => 'sem@example.com', 'weights' => ['can' => 35, 'maybe' => 25, 'cannot' => 40]],
        ['name' => 'Iris van Leeuwen', 'email' => 'iris@example.com', 'weights' => ['can' => 70, 'maybe' => 20, 'cannot' => 10]],
    ];

    /**
     * A stretch everybody marks as "can", so the suggested date ranges have a
     * clear winner to surface.
     */
    protected const SHARED_WINDOW = ['2027-02-13', '2027-02-21'];

    public function run(int $vacationId = 1): void
    {
        $vacation = Vacation::find($vacationId);

        if (! $vacation) {
            $this->command?->warn("Vacation {$vacationId} not found - nothing seeded.");

            return;
        }

        $dates = $vacation->planningDates();

        if (empty($dates)) {
            $this->command?->warn("Vacation {$vacationId} has no planning range - nothing seeded.");

            return;
        }

        [$sharedStart, $sharedEnd] = self::SHARED_WINDOW;

        foreach ($this->participants as $participant) {
            $user = User::firstWhere('email', $participant['email'])
                ?? User::factory()->create([
                    'name' => $participant['name'],
                    'email' => $participant['email'],
                ]);

            $vacation->users()->syncWithoutDetaching([$user->id]);

            foreach ($dates as $date) {
                $status = $date >= $sharedStart && $date <= $sharedEnd
                    ? AvailabilityStatus::Can
                    : $this->pickStatus($participant['weights'], $date);

                VacationDateAvailability::firstOrCreate(
                    ['vacation_id' => $vacation->id, 'user_id' => $user->id, 'date' => $date],
                    ['status' => $status],
                );
            }
        }

        $this->command?->info(sprintf(
            'Seeded %d participants x %d dates on "%s".',
            count($this->participants),
            count($dates),
            $vacation->name,
        ));
    }

    /**
     * Weighted pick, with weekends nudged towards "can" - people are more
     * likely to be free on a Saturday than on a random Tuesday.
     *
     * @param  array<string, int>  $weights
     */
    protected function pickStatus(array $weights, string $date): AvailabilityStatus
    {
        if (in_array((int) date('N', strtotime($date)), [5, 6, 7], true)) {
            $weights['can'] += 25;
        }

        $roll = random_int(1, array_sum($weights));

        foreach ($weights as $value => $weight) {
            $roll -= $weight;

            if ($roll <= 0) {
                return AvailabilityStatus::from($value);
            }
        }

        return AvailabilityStatus::Maybe;
    }
}
