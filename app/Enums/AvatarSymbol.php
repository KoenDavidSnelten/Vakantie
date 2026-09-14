<?php

namespace App\Enums;

enum AvatarSymbol: string
{
    case Initials = 'initials';
    case Ski = 'ski';
    case Snowboard = 'snowboard';
    case Mountain = 'mountain';
    case Snowman = 'snowman';
    case Snowflake = 'snowflake';
    case Gondola = 'gondola';
    case Sled = 'sled';
    case Fox = 'fox';
    case Bear = 'bear';
    case Beer = 'beer';
    case Fire = 'fire';
    case Sun = 'sun';

    public function label(): string
    {
        return match ($this) {
            self::Initials => 'Je initialen',
            self::Ski => 'Ski',
            self::Snowboard => 'Snowboard',
            self::Mountain => 'Berg',
            self::Snowman => 'Sneeuwpop',
            self::Snowflake => 'Sneeuwvlok',
            self::Gondola => 'Gondel',
            self::Sled => 'Slee',
            self::Fox => 'Vos',
            self::Bear => 'Beer',
            self::Beer => 'Biertje',
            self::Fire => 'Haardvuur',
            self::Sun => 'Zon',
        };
    }

    /**
     * Leeg voor Initials: die tekent de component uit de naam van de gebruiker.
     */
    public function glyph(): string
    {
        return match ($this) {
            self::Initials => '',
            self::Ski => '⛷️',
            self::Snowboard => '🏂',
            self::Mountain => '🏔️',
            self::Snowman => '⛄',
            self::Snowflake => '❄️',
            self::Gondola => '🚠',
            self::Sled => '🛷',
            self::Fox => '🦊',
            self::Bear => '🐻',
            self::Beer => '🍻',
            self::Fire => '🔥',
            self::Sun => '🌞',
        };
    }
}
