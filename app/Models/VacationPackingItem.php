<?php

namespace App\Models;

use App\Enums\PackingCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacationPackingItem extends Model
{
    protected $fillable = [
        'vacation_id',
        'user_id',
        'category',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'category' => PackingCategory::class,
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

    public function checks(): HasMany
    {
        return $this->hasMany(VacationPackingItemCheck::class);
    }

    public function isCheckedBy(int $userId): bool
    {
        return $this->checks->contains('user_id', $userId);
    }
}
