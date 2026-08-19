<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationSkiAreaVote extends Model
{
    protected $fillable = [
        'vacation_ski_area_id',
        'user_id',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }

    public function skiArea(): BelongsTo
    {
        return $this->belongsTo(VacationSkiArea::class, 'vacation_ski_area_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
