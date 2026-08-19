<?php

namespace App\Models;

use App\Enums\TravelOptionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationTravelOption extends Model
{
    protected $fillable = [
        'vacation_id',
        'user_id',
        'type',
        'name',
        'url',
        'price_per_person',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => TravelOptionType::class,
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
}
