<?php

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VacationVehicle extends Model
{
    protected $fillable = [
        'vacation_id',
        'user_id',
        'type',
        'car_model',
        'seats',
        'has_winter_tires',
        'has_large_trunk',
        'has_roof_box',
        'price_per_day',
    ];

    protected function casts(): array
    {
        return [
            'type' => VehicleType::class,
            'seats' => 'integer',
            'has_winter_tires' => 'boolean',
            'has_large_trunk' => 'boolean',
            'has_roof_box' => 'boolean',
            'price_per_day' => 'decimal:2',
        ];
    }

    public function vacation(): BelongsTo
    {
        return $this->belongsTo(Vacation::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Wie er in deze auto meerijdt (de chauffeur telt gewoon mee als inzittende).
     */
    public function passengers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vacation_vehicle_passengers')
            ->withTimestamps()
            ->orderBy('name');
    }

    /**
     * Hoeveel plekken er nog vrij zijn, of null als het aantal zitplaatsen
     * niet is ingevuld.
     */
    public function seatsLeft(): ?int
    {
        return $this->seats === null ? null : max(0, $this->seats - $this->passengers->count());
    }

    public function isFull(): bool
    {
        return $this->seatsLeft() === 0;
    }
}
