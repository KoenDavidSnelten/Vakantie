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
            self::Planning => 'De reis krijgt vorm. Prik samen een datum in de datumplanner en stem in de locatieplanner op skigebieden en hotels.',
            self::Booking => 'Datum en locatie staan vast. Nu worden de hotels geboekt, de prijzen per persoon kloppen pas als het aantal deelnemers bekend is.',
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
     * The date/ski-area/travel planner pages are reachable during these
     * phases; hotels specifically only become bookable once Booking starts,
     * since the participant count (needed for per-person prices) isn't
     * reliable until then.
     */
    public function hasPlannerAccess(): bool
    {
        return in_array($this, [self::Planning, self::Booking, self::TravelPlanning], true);
    }
}
