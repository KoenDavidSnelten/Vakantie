<?php

namespace App\Models;

use App\Enums\PriceUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacationHotel extends Model
{
    protected $fillable = [
        'vacation_ski_area_id',
        'user_id',
        'name',
        'url',
        'image_url',
        'price_accommodation_per_night',
        'price_accommodation_unit',
        'room_layout',
    ];

    protected function casts(): array
    {
        return [
            'price_accommodation_per_night' => 'decimal:2',
            'price_accommodation_unit' => PriceUnit::class,
        ];
    }

    public function skiArea(): BelongsTo
    {
        return $this->belongsTo(VacationSkiArea::class, 'vacation_ski_area_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(VacationHotelVote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(VacationHotelComment::class)->oldest();
    }

    public function score(): int
    {
        return $this->votes->sum('value');
    }

    /**
     * A single comparable figure per person per night: this hotel's
     * accommodation price plus its ski area's ski pass, spread over a night.
     */
    public function totalPricePerNight(int $participants): ?float
    {
        $participants = max($participants, 1);

        $accommodation = null;
        if ($this->price_accommodation_per_night !== null) {
            $accommodation = $this->price_accommodation_unit === PriceUnit::PerPerson
                ? (float) $this->price_accommodation_per_night
                : (float) $this->price_accommodation_per_night / $participants;
        }

        $skiPass = $this->skiArea->pricePerNightSkiPass();

        if ($accommodation === null && $skiPass === null) {
            return null;
        }

        return round(($accommodation ?? 0) + ($skiPass ?? 0), 2);
    }
}
