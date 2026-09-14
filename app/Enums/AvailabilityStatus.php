<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Can = 'can';
    case Maybe = 'maybe';
    case Cannot = 'cannot';

    public function label(): string
    {
        return match ($this) {
            self::Can => 'Kan',
            self::Maybe => 'Misschien',
            self::Cannot => 'Kan niet',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Can => 'bg-emerald-100 text-emerald-900 ring-1 ring-emerald-400',
            self::Maybe => 'bg-orange-100 text-orange-900 ring-1 ring-orange-400',
            self::Cannot => 'bg-rose-100 text-rose-900 ring-1 ring-rose-400',
        };
    }

    /**
     * Enkel de vulkleur, voor het overzichtsrooster waar de kleur zelf het label is.
     */
    public function solidClasses(): string
    {
        return match ($this) {
            self::Can => 'bg-emerald-500',
            self::Maybe => 'bg-orange-500',
            self::Cannot => 'bg-rose-500',
        };
    }

    /**
     * Solid color used for the calendar day-picker swatches.
     */
    public function dotClasses(): string
    {
        return match ($this) {
            self::Can => 'bg-emerald-500 hover:bg-emerald-400',
            self::Maybe => 'bg-orange-500 hover:bg-orange-400',
            self::Cannot => 'bg-rose-500 hover:bg-rose-400',
        };
    }

    /**
     * Outlined when unselected, filled solid when selected (via the `peer-checked` variant).
     */
    public function swatchClasses(): string
    {
        return match ($this) {
            self::Can => 'border-emerald-500 peer-checked:bg-emerald-500',
            self::Maybe => 'border-orange-500 peer-checked:bg-orange-500',
            self::Cannot => 'border-rose-500 peer-checked:bg-rose-500',
        };
    }
}
