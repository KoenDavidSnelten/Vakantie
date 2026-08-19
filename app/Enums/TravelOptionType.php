<?php

namespace App\Enums;

enum TravelOptionType: string
{
    case Ov = 'ov';
    case Bus = 'bus';
    case Trein = 'trein';

    public function label(): string
    {
        return match ($this) {
            self::Ov => 'OV',
            self::Bus => 'Bus',
            self::Trein => 'Trein',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Ov => '🚇',
            self::Bus => '🚌',
            self::Trein => '🚆',
        };
    }
}
