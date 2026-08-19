<?php

namespace App\Enums;

enum PriceUnit: string
{
    case Total = 'total';
    case PerPerson = 'per_person';

    public function label(): string
    {
        return match ($this) {
            self::Total => 'Totaalprijs',
            self::PerPerson => 'Per persoon',
        };
    }
}
