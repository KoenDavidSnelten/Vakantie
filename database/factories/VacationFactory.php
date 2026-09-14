<?php

namespace Database\Factories;

use App\Enums\VacationPhase;
use App\Models\Vacation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacation>
 */
class VacationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $planningStart = fake()->dateTimeBetween('+1 month', '+3 months');
        $planningEnd = (clone $planningStart)->modify('+2 months');

        return [
            'name' => 'Wintersport '.fake()->year(),
            'description' => fake()->sentence(),
            'phase' => VacationPhase::Planning,
            'planning_start_date' => $planningStart->format('Y-m-d'),
            'planning_end_date' => $planningEnd->format('Y-m-d'),
            'final_start_date' => null,
            'final_end_date' => null,
        ];
    }
}
