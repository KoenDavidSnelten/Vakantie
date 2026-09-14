<x-app-layout :title="$vacation->name">
    <x-slot name="header">
        <div class="space-y-3">
            <a href="{{ route('vacations.index') }}" class="inline-block rounded text-sm text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">
                &larr; {{ __('Vakanties') }}
            </a>

            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                    {{ $vacation->name }}
                </h2>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('vacations.edit', $vacation) }}" class="rounded text-sm text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        {{ __('Bewerken') }}
                    </a>
                @endif
            </div>

            @include('vacations.partials.phase-stepper')
        </div>
    </x-slot>

    @php
        $plannersOpen = $vacation->phase->hasPlannerAccess();

        // De paklijst blijft in élke fase open, de andere planners sluiten zodra
        // de vakantie begint. Gesloten planners blijven zichtbaar mét reden, zodat
        // ze niet zomaar lijken te verdwijnen.
        $planners = [
            ['route' => 'vacations.date-planner', 'icon' => '📅', 'label' => 'Datumplanner', 'hint' => 'Prik samen een datum', 'locks' => true],
            ['route' => 'vacations.locations.index', 'icon' => '📍', 'label' => 'Locatieplanner', 'hint' => 'Skigebieden en hotels', 'locks' => true],
            ['route' => 'vacations.travel-planner', 'icon' => '🚗', 'label' => 'Reisplanner', 'hint' => "Auto's, OV en wie rijdt met wie", 'locks' => true],
            ['route' => 'vacations.packing-list', 'icon' => '🧳', 'label' => 'Paklijst', 'hint' => 'Vink af wat je hebt ingepakt', 'locks' => false],
        ];
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Informatie staat er altijd: ook tijdens het plannen wil je de omschrijving kunnen lezen. --}}
            <div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200 rounded-2xl">
                <h3 class="text-lg font-bold text-slate-900">{{ __('Informatie') }}</h3>

                @if ($vacation->final_start_date && $vacation->final_end_date)
                    <p class="mt-2 text-sm font-semibold text-sky-800">
                        📅 {{ $vacation->final_start_date->locale('nl')->translatedFormat('j F Y') }}
                        t/m {{ $vacation->final_end_date->locale('nl')->translatedFormat('j F Y') }}
                    </p>
                @elseif ($vacation->planning_start_date && $vacation->planning_end_date)
                    <p class="mt-2 text-sm text-slate-600">
                        📅 Datum nog niet geprikt &ndash; we peilen
                        {{ $vacation->planning_start_date->locale('nl')->translatedFormat('j M') }}
                        t/m {{ $vacation->planning_end_date->locale('nl')->translatedFormat('j M Y') }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-slate-600">📅 Er is nog geen datumbereik ingesteld.</p>
                @endif

                <p class="mt-3 text-slate-700 whitespace-pre-line">
                    {{ $vacation->description ?: __('Nog geen informatie toegevoegd.') }}
                </p>

                @if ($vacation->users->isNotEmpty())
                    <div class="mt-4 flex flex-wrap items-center gap-1.5 border-t border-slate-100 pt-4">
                        <span class="mr-1 text-xs font-bold uppercase tracking-wide text-slate-600">Deelnemers</span>
                        @foreach ($vacation->users as $participant)
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                {{ $participant->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Planners --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($planners as $planner)
                    @php $locked = $planner['locks'] && ! $plannersOpen; @endphp

                    @if ($locked)
                        <div class="rounded-2xl bg-slate-100 p-6 ring-1 ring-slate-200" aria-disabled="true">
                            <p class="text-3xl grayscale" aria-hidden="true">{{ $planner['icon'] }}</p>
                            <p class="mt-4 text-lg font-semibold text-slate-500">{{ $planner['label'] }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                🔒 Gesloten in de fase &ldquo;{{ $vacation->phase->label() }}&rdquo;
                            </p>
                        </div>
                    @else
                        <a
                            href="{{ route($planner['route'], $vacation) }}"
                            class="block bg-white p-6 shadow-sm ring-1 ring-slate-200 rounded-2xl transition hover:ring-sky-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500"
                        >
                            <p class="text-3xl" aria-hidden="true">{{ $planner['icon'] }}</p>
                            <p class="mt-4 text-lg font-semibold text-slate-900">{{ $planner['label'] }}</p>
                            <p class="mt-1 text-xs text-slate-600">{{ $planner['hint'] }}</p>
                        </a>
                    @endif
                @endforeach
            </div>

            @unless ($plannersOpen)
                <p class="text-sm text-slate-600">
                    De planners gaan dicht zodra de vakantie begint &ndash; alles wat er is ingevuld blijft bewaard.
                    De paklijst blijft wel gewoon werken.
                </p>
            @endunless
        </div>
    </div>
</x-app-layout>
