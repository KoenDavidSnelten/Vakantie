<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Drankspellen') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Picolo --}}
                <a href="{{ route('games.picolo') }}"
                    class="group relative flex flex-col overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 p-6 text-white shadow-lg ring-1 ring-black/5 transition hover:-translate-y-1 hover:shadow-xl">
                    <span class="text-4xl drop-shadow-sm">🥃</span>
                    <p class="mt-4 text-xl font-bold">Picolo</p>
                    <p class="mt-1 text-sm text-white/80">Vul namen in en tik door de kaartjes, opdrachten, stemrondes, regels en duels.</p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-white/90">
                        Spelen <span class="transition group-hover:translate-x-0.5">→</span>
                    </span>
                </a>

                {{-- Boom It --}}
                <a href="{{ route('games.boom-it') }}"
                    class="group relative flex flex-col overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-6 text-white shadow-lg ring-1 ring-black/5 transition hover:-translate-y-1 hover:shadow-xl">
                    <span class="text-4xl drop-shadow-sm">💥</span>
                    <p class="mt-4 text-xl font-bold">Boom It</p>
                    <p class="mt-1 text-sm text-white/80">Geef de telefoon door… wie 'm vasthoudt als de bom afgaat, drinkt.</p>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-white/90">
                        Spelen <span class="transition group-hover:translate-x-0.5">→</span>
                    </span>
                </a>

                {{-- Broers --}}
                <div class="relative flex flex-col overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-6 text-white shadow-lg ring-1 ring-black/5">
                    <span class="text-4xl drop-shadow-sm">🍻</span>
                    <p class="mt-4 text-xl font-bold">Broers</p>
                    <p class="mt-1 text-sm text-white/80">Het klassieke kaartspel. Binnenkort speelbaar.</p>
                    <span class="mt-4 inline-flex w-max items-center rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold">
                        Binnenkort
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
