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
        // Sterkere wachtwoordeisen voor registratie, wachtwoord-reset en -wijzigen.
        // Minimaal 10 tekens, hoofd- en kleine letters, cijfers en symbolen, en
        // niet voorkomend in bekende datalekken (fails open als de check niet lukt).
        Password::defaults(function () {
            return Password::min(10)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });
    }
}
