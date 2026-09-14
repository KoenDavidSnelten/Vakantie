<?php

namespace App\Enums;

enum AvatarFrame: string
{
    case None = 'none';
    case Light = 'light';
    case Dark = 'dark';
    case Gold = 'gold';
    case Halo = 'halo';
    case Dashed = 'dashed';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Geen rand',
            self::Light => 'Wit',
            self::Dark => 'Donker',
            self::Gold => 'Goud',
            self::Halo => 'Halo',
            self::Dashed => 'Gestippeld',
        };
    }

    /**
     * Voluit geschreven klassenamen, net als bij AvatarColor: Tailwind haalt
     * samengestelde namen uit de productie-build weg.
     */
    public function frameClass(): string
    {
        return match ($this) {
            self::None => '',
            self::Light => 'ring-2 ring-white',
            self::Dark => 'ring-2 ring-slate-900',
            self::Gold => 'ring-2 ring-amber-400',
            self::Halo => 'ring-4 ring-slate-200',
            self::Dashed => 'border-2 border-dashed border-white',
        };
    }
}
