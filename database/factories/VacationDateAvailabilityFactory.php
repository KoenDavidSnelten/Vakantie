<?php

namespace Database\Factories;

use App\Enums\AvailabilityStatus;
use App\Models\User;
use App\Models\Vacation;
use App\Models\VacationDateAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VacationDateAvailability>
 */
class VacationDateAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vacation_id' => Vacation::factory(),
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'status' => fake()->randomElement(AvailabilityStatus::cases()),
        ];
    }

    public function status(AvailabilityStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
