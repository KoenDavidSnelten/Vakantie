<?php

namespace App\Enums;

enum PackingCategory: string
{
    case WinterSportClothing = 'wintersport_clothing';
    case Toiletries = 'toiletries';
    case RegularClothing = 'regular_clothing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WinterSportClothing => 'Wintersportkleding',
            self::Toiletries => 'Verzorging',
            self::RegularClothing => 'Normale kleding',
            self::Other => 'Overig',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WinterSportClothing => '🎿',
            self::Toiletries => '🧴',
            self::RegularClothing => '👕',
            self::Other => '📦',
        };
    }
}
