<?php

namespace App\Enums;

enum VacationPhase: string
{
    case Planning = 'planning';
    case Booking = 'booking';
    case TravelPlanning = 'travel_planning';
    case OnVacation = 'on_vacation';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Plan',
            self::Booking => 'Boek',
            self::TravelPlanning => 'Reisplan',
            self::OnVacation => 'Vakantie',
            self::Finished => 'Afgerond',
        };
    }

    /**
     * A short explanation of what happens during this phase and what you can
     * do in it. Shown in the info popover next to the phase stepper.
     */
    public function description(): string
    {
        return match ($this) {
            self::Planning => 'De reis krijgt vorm. Prik samen een datum in de datumplanner en verzamel in de locatieplanner alvast skigebieden en hotels, zodat je op je favorieten kunt stemmen.',
            self::Booking => 'Datum en locatie staan vast. Werk de hotelprijzen nog even bij (die kloppen pas als de datum en het aantal deelnemers vaststaan) en boek daarna het gekozen hotel.',
            self::TravelPlanning => 'Regel het vervoer in de reisplanner: voeg voertuigen en reisopties toe en verdeel wie met wie meerijdt.',
            self::OnVacation => 'Het is zover! De planners zijn afgesloten; gebruik de paklijst zodat niemand iets vergeet.',
            self::Finished => 'De vakantie zit erop. Je kunt alles nog als naslag terugkijken.',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Planning => 'bg-amber-100 text-amber-800',
            self::Booking => 'bg-indigo-100 text-indigo-700',
            self::TravelPlanning => 'bg-violet-100 text-violet-700',
            self::OnVacation => 'bg-sky-100 text-sky-700',
            self::Finished => 'bg-emerald-100 text-emerald-700',
        };
    }

    /**
     * The date/ski-area/travel planner pages (including adding hotels) are
     * reachable during these phases, so hotels can already be scouted while
     * planning; their prices get updated again in the booking phase, once the
     * date and participant count are final.
     */
    public function hasPlannerAccess(): bool
    {
        return in_array($this, [self::Planning, self::Booking, self::TravelPlanning], true);
    }
}
