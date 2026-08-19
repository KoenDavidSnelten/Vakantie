<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class VacationSkiArea extends Model
{
    /**
     * The ski pass price is collected as a flat price for this many days.
     */
    public const SKI_PASS_DAYS = 5;

    protected $fillable = [
        'vacation_id',
        'user_id',
        'name',
        'price_ski_pass',
        'distance_to_slopes_km',
        'has_bus',
        'ski_area_map_path',
        'ski_area_map_url',
    ];

    protected function casts(): array
    {
        return [
            'price_ski_pass' => 'decimal:2',
            'distance_to_slopes_km' => 'decimal:1',
            'has_bus' => 'boolean',
        ];
    }

    /**
     * Resolves to the uploaded map's public URL when one was uploaded,
     * otherwise falls back to the manually pasted link (if any).
     */
    protected function skiAreaMapUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->ski_area_map_path ? Storage::disk('public')->url($this->ski_area_map_path) : $value,
        );
    }

    public function vacation(): BelongsTo
    {
        return $this->belongsTo(Vacation::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(VacationHotel::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(VacationSkiAreaVote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(VacationSkiAreaComment::class)->oldest();
    }

    public function score(): int
    {
        return $this->votes->sum('value');
    }

    /**
     * The 5-day ski pass price spread over one night, used to fold into a
     * hotel's total price per night.
     */
    public function pricePerNightSkiPass(): ?float
    {
        return $this->price_ski_pass === null
            ? null
            : round((float) $this->price_ski_pass / self::SKI_PASS_DAYS, 2);
    }
}
