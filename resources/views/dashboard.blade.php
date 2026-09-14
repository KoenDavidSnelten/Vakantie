<x-app-layout title="Dashboard">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <p class="text-lg font-semibold text-slate-900">
                Hoi {{ auth()->user()->name }} 👋
            </p>

            @if (! $vacation)
                <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white/50 p-12 text-center">
                    <p class="text-4xl">🧳</p>
                    <p class="mt-4 font-semibold text-slate-800">Je zit nog niet bij een reis</p>
                    <p class="mt-2 text-sm text-slate-600">
                        @if (auth()->user()->isAdmin())
                            Maak een vakantie aan om te beginnen met plannen.
                        @else
                            Vraag de beheerder om je toe te voegen aan de vakantie. Zodra dat gebeurd is, staat hier wat er van je verwacht wordt.
                        @endif
                    </p>

                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('vacations.create') }}" class="mt-6 inline-flex items-center rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                            Nieuwe vakantie
                        </a>
                    @endif
                </div>
            @else
                {{-- De lopende reis --}}
                <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $vacation->phase->badgeClasses() }}">
                                {{ $vacation->phase->label() }}
                            </span>
                            <h3 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">{{ $vacation->name }}</h3>

                            @if ($vacation->final_start_date && $vacation->final_end_date)
                                <p class="mt-1 text-sm font-semibold text-sky-800">
                                    📅 {{ $vacation->final_start_date->locale('nl')->translatedFormat('j F Y') }}
                                    t/m {{ $vacation->final_end_date->locale('nl')->translatedFormat('j F Y') }}
                                </p>
                            @elseif ($vacation->planning_start_date && $vacation->planning_end_date)
                                <p class="mt-1 text-sm text-slate-600">
                                    📅 Datum nog niet geprikt &ndash; we peilen
                                    {{ $vacation->planning_start_date->locale('nl')->translatedFormat('j M') }}
                                    t/m {{ $vacation->planning_end_date->locale('nl')->translatedFormat('j M Y') }}
                                </p>
                            @else
                                <p class="mt-1 text-sm text-slate-600">📅 Datum nog niet geprikt</p>
                            @endif
                        </div>

                        @if ($daysUntilDeparture !== null)
                            <div class="rounded-2xl bg-sky-50 px-5 py-3 text-center ring-1 ring-sky-200">
                                <p class="text-3xl font-black leading-none text-sky-800">{{ $daysUntilDeparture }}</p>
                                <p class="mt-1 text-xs font-bold uppercase tracking-wide text-sky-700">
                                    {{ $daysUntilDeparture === 1 ? 'dag te gaan' : 'dagen te gaan' }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $vacation->phase->description() }}</p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('vacations.show', $vacation) }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                            Open de vakantie
                        </a>
                        <a href="{{ route('vacations.index') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                            Alle vakanties
                        </a>
                    </div>
                </div>

                {{-- Wat wordt er nu van jou verwacht --}}
                @if ($tasks->isNotEmpty())
                    <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200">
                        <h3 class="text-lg font-bold text-slate-900">Wat kun jij nu doen?</h3>

                        <ul class="mt-4 space-y-2">
                            @foreach ($tasks as $task)
                                <li>
                                    <a
                                        href="{{ $task['route'] }}"
                                        class="flex items-center gap-3 rounded-xl p-3 ring-1 transition focus:outline-none focus:ring-2 focus:ring-sky-500 {{ $task['done'] ? 'bg-emerald-50 ring-emerald-200 hover:bg-emerald-100' : 'bg-slate-50 ring-slate-200 hover:bg-slate-100' }}"
                                    >
                                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {{ $task['done'] ? 'bg-emerald-600' : 'bg-slate-400' }}" aria-hidden="true">
                                            {{ $task['done'] ? '✓' : '•' }}
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-semibold text-slate-900">{{ $task['label'] }}</span>
                                            <span class="block text-xs text-slate-600">{{ $task['hint'] }}</span>
                                        </span>
                                        <span class="flex-shrink-0 text-slate-400" aria-hidden="true">&rarr;</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Paklijst-voortgang --}}
                @if ($packingProgress)
                    <a href="{{ route('vacations.packing-list', $vacation) }}" class="block rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:ring-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-bold text-slate-900">🧳 Jouw paklijst</h3>
                            <p class="text-sm font-semibold text-slate-700">{{ $packingProgress['checked'] }} / {{ $packingProgress['total'] }} ingepakt</p>
                        </div>

                        <div class="mt-3 h-2.5 w-full overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuenow="{{ $packingProgress['percentage'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Paklijst-voortgang">
                            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $packingProgress['percentage'] }}%"></div>
                        </div>
                    </a>
                @endif
            @endif

            {{-- Eerdere reizen --}}
            @if ($finishedVacations->isNotEmpty())
                <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-base font-bold text-slate-900">Eerdere reizen</h3>

                    <ul class="mt-3 divide-y divide-slate-100">
                        @foreach ($finishedVacations as $past)
                            <li>
                                <a href="{{ route('vacations.show', $past) }}" class="flex items-center justify-between gap-3 rounded-lg py-2.5 text-sm transition hover:text-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    <span class="font-semibold text-slate-800">{{ $past->name }}</span>
                                    @if ($past->final_start_date)
                                        <span class="text-slate-600">{{ $past->final_start_date->format('Y') }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
