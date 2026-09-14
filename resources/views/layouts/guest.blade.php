<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $attributes->get('title') ? $attributes->get('title').' · '.config('app.name', 'Vakantie') : config('app.name', 'Vakantie') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-slate-800">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50">
            <div>
                <a href="/" class="flex items-center gap-2 text-slate-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    <x-application-logo class="w-10 h-10 text-sky-600" />
                    <span class="text-xl font-bold tracking-tight">{{ config('app.name', 'Vakantie') }}</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
