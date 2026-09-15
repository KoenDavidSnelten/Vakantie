<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alle verzoeken komen binnen via de proxy-container; die is als
        // enige aan poort 80 en 443 geknoopt. Daarom mogen we zijn
        // X-Forwarded-headers geloven -- iets anders dan die proxy kan de app
        // niet bereiken. Zonder dit denkt Laravel dat het verzoek over http
        // binnenkwam en zet het http:// in elke gegenereerde URL, waarna de
        // browser de stylesheets en scripts als mixed content blokkeert.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
