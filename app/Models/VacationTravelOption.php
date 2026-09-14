<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationTravelOption extends Model
{
    protected $fillable = [
        'vacation_id',
        'user_id',
        'vacation_ski_area_id',
        'name',
        'url',
        'price_per_person',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price_per_person' => 'decimal:2',
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
     * Het skigebied waar deze reisoptie heen gaat, gekozen uit de gebieden die
     * al in de locatieplanner staan. Null als de bestemming nog openligt.
     */
    public function skiArea(): BelongsTo
    {
        return $this->belongsTo(VacationSkiArea::class, 'vacation_ski_area_id');
    }
}
