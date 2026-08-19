<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationPackingItemCheck extends Model
{
    protected $fillable = [
        'vacation_packing_item_id',
        'user_id',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(VacationPackingItem::class, 'vacation_packing_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
