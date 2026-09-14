<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use Database\Factories\VacationDateAvailabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationDateAvailability extends Model
{
    /** @use HasFactory<VacationDateAvailabilityFactory> */
    use HasFactory;

    protected $fillable = [
        'vacation_id',
        'user_id',
        'date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'status' => AvailabilityStatus::class,
        ];
    }

    public function vacation(): BelongsTo
    {
        return $this->belongsTo(Vacation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
