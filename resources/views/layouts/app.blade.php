<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Elke pagina geeft zijn eigen titel mee via <x-app-layout title="..."> --}}
        <title>{{ $attributes->get('title') ? $attributes->get('title').' · '.config('app.name', 'Vakantie') : config('app.name', 'Vakantie') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-slate-800">
        <a href="#hoofdinhoud" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-sky-700 focus:shadow-lg focus:ring-2 focus:ring-sky-500">
            Direct naar de inhoud
        </a>

        <div class="min-h-screen bg-slate-200">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-md border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <x-flash-message />

            <!-- Page Content -->
            <main id="hoofdinhoud">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
