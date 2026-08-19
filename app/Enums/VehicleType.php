<?php

namespace App\Enums;

enum VehicleType: string
{
    case Own = 'own';
    case Rental = 'rental';

    public function label(): string
    {
        return match ($this) {
            self::Own => 'Eigen auto',
            self::Rental => 'Huurauto',
        };
    }
}
