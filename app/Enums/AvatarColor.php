<?php

namespace App\Enums;

use App\Models\User;

enum AvatarColor: string
{
    case Sky = 'sky';
    case Emerald = 'emerald';
    case Amber = 'amber';
    case Rose = 'rose';
    case Violet = 'violet';
    case Teal = 'teal';
    case Indigo = 'indigo';
    case Orange = 'orange';
    case Cyan = 'cyan';
    case Fuchsia = 'fuchsia';
    case Lime = 'lime';
    case Slate = 'slate';

    public function label(): string
    {
        return match ($this) {
            self::Sky => 'Lucht',
            self::Emerald => 'Smaragd',
            self::Amber => 'Amber',
            self::Rose => 'Roze',
            self::Violet => 'Violet',
            self::Teal => 'Turkoois',
            self::Indigo => 'Indigo',
            self::Orange => 'Oranje',
            self::Cyan => 'Cyaan',
            self::Fuchsia => 'Fuchsia',
            self::Lime => 'Limoen',
            self::Slate => 'Leigrijs',
        };
    }

    /**
     * Voluit geschreven klassenamen: Tailwind scant de broncode als platte
     * tekst, dus samengestelde namen ('bg-'.$kleur.'-600') zouden uit de
     * productie-build gefilterd worden.
     */
    public function backgroundClass(): string
    {
        return match ($this) {
            self::Sky => 'bg-sky-600',
            self::Emerald => 'bg-emerald-600',
            self::Amber => 'bg-amber-600',
            self::Rose => 'bg-rose-600',
            self::Violet => 'bg-violet-600',
            self::Teal => 'bg-teal-600',
            self::Indigo => 'bg-indigo-600',
            self::Orange => 'bg-orange-600',
            self::Cyan => 'bg-cyan-600',
            self::Fuchsia => 'bg-fuchsia-600',
            self::Lime => 'bg-lime-600',
            self::Slate => 'bg-slate-600',
        };
    }

    /**
     * De kleur die iemand krijgt zolang hij er zelf geen gekozen heeft. Op het
     * e-mailadres en niet op de naam, zodat de kleur blijft staan als iemand
     * zijn naam wijzigt.
     */
    public static function defaultFor(User $user): self
    {
        $cases = self::cases();

        return $cases[abs(crc32((string) $user->email)) % count($cases)];
    }
}
