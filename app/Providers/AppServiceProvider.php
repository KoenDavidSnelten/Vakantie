<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Wachtwoordeisen voor registratie, wachtwoord-reset en -wijzigen.
        //
        // Bewust géén eisen aan hoofdletters, cijfers of symbolen: die leveren
        // vooral voorspelbare wachtwoorden op ("Wachtwoord1!") en kosten meer
        // aan afhakende gebruikers dan ze aan veiligheid opleveren. NIST
        // SP 800-63B raadt ze om die reden af.
        //
        // Wat overblijft is wat wél werkt: een ondergrens aan de lengte en een
        // controle tegen bekende datalekken, want hergebruikte wachtwoorden
        // zijn hoe accounts in de praktijk overgenomen worden. Die controle
        // kost niets voor wie een uniek wachtwoord kiest, en fails open als
        // de dienst onbereikbaar is.
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->uncompromised();
        });
    }
}
