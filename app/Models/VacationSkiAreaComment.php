<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationSkiAreaComment extends Model
{
    protected $fillable = [
        'vacation_ski_area_id',
        'user_id',
        'body',
    ];

    public function skiArea(): BelongsTo
    {
        return $this->belongsTo(VacationSkiArea::class, 'vacation_ski_area_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
