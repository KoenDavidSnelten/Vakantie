<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationHotelComment extends Model
{
    protected $fillable = [
        'vacation_hotel_id',
        'user_id',
        'body',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(VacationHotel::class, 'vacation_hotel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
