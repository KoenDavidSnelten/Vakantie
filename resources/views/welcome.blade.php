<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Vakantie') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-white text-slate-800">

        <!-- Header -->
        <header class="fixed inset-x-0 top-0 z-30 bg-slate-900 shadow-sm shadow-black/10">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <nav class="flex items-center justify-between py-6">
                    <a href="/" class="flex items-center gap-2 text-white">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C8 6 5 10.5 5 14.5C5 18.09 8.13 21 12 21C15.87 21 19 18.09 19 14.5C19 10.5 16 6 12 2Z" fill="currentColor" fill-opacity="0.9"/>
                        </svg>
                        <span class="text-xl font-bold tracking-tight">{{ config('app.name', 'Vakantie') }}</span>
                    </a>
                    <div class="flex items-center gap-3">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="rounded-full bg-white px-5 py-2 text-sm font-semibold text-sky-700 shadow-sm hover:bg-sky-50 transition">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-semibold text-white/90 hover:text-white transition">
                                    Inloggen
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="rounded-full bg-white px-5 py-2 text-sm font-semibold text-sky-700 shadow-sm hover:bg-sky-50 transition">
                                        Account maken
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative isolate overflow-hidden bg-slate-900 min-h-[560px] flex items-center">
            <img
                src="https://images.unsplash.com/photo-1551524559-8af4e6624178?q=80&w=2400&auto=format&fit=crop"
                alt="Besneeuwde bergtoppen"
                class="absolute inset-0 -z-10 h-full w-full object-cover"
            >
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>

            <div class="mx-auto max-w-6xl px-6 lg:px-8 pt-28 pb-16 w-full text-center">
                <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur">
                    Onze vakanties, op één plek
                </span>
                <h1 class="mt-6 text-4xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    {{ config('app.name', 'Vakantie') }}
                </h1>
                <p class="mt-4 text-lg text-slate-200 max-w-xl mx-auto">
                    Hier verzamelen we onze reizen, plannen en herinneringen. Gewoon voor ons.
                </p>
            </div>
        </section>

        <!-- Next trip -->
        <section class="py-20 bg-white">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-sky-600">Volgende reis</h2>
                    <p class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Wintersport 2027 ❄️</p>
                </div>

                <div class="mt-10 rounded-2xl bg-slate-50 ring-1 ring-slate-100 p-8 sm:p-10 text-center">
                    <p class="text-5xl">🏔️</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900">Save the date, details volgen nog</p>
                    <p class="mt-2 text-slate-600">Bestemming en datum zijn nog niet vastgelegd. Zodra we het weten, komt het hier te staan.</p>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="mt-6 inline-block rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-sky-500 transition">
                                Ga naar dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="mt-6 inline-block rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-sky-500 transition">
                                Log in om mee te plannen
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </section>

        <!-- Past trips -->
        <section class="py-20 bg-slate-50">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-sky-600">Eerder</h2>
                    <p class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Vorige vakanties</p>
                </div>

                <div class="mt-10 rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
                    <p class="text-4xl">📷</p>
                    <p class="mt-4 font-semibold text-slate-700">Nog niets hier</p>
                    <p class="mt-2 text-sm text-slate-500">Wintersport 2027 is onze eerste. Zodra we terug zijn, verschijnen hier foto's en herinneringen.</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-400">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 py-10">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C8 6 5 10.5 5 14.5C5 18.09 8.13 21 12 21C15.87 21 19 18.09 19 14.5C19 10.5 16 6 12 2Z" fill="currentColor" fill-opacity="0.9"/>
                        </svg>
                        <span class="font-bold">{{ config('app.name', 'Vakantie') }}</span>
                    </div>
                    <p class="text-sm">Gemaakt voor ons groepje, {{ date('Y') }}</p>
                </div>
            </div>
        </footer>

    </body>
</html>
