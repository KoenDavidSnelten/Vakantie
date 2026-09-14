<x-app-layout title="Vakanties">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                {{ __('Vakanties') }}
            </h2>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('vacations.create') }}" class="inline-flex items-center px-5 py-2.5 bg-sky-600 rounded-lg font-semibold text-sm text-white shadow-sm transition hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    {{ __('Nieuwe vakantie') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($vacations->isEmpty())
                <div class="rounded-2xl border-2 border-dashed border-slate-300 p-12 text-center">
                    <p class="text-4xl">🧳</p>
                    <p class="mt-4 font-semibold text-slate-800">Nog geen vakanties</p>
                    <p class="mt-2 text-sm text-slate-600">
                        @if (auth()->user()->isAdmin())
                            Maak de eerste vakantie aan om te beginnen met plannen.
                        @else
                            Vraag de beheerder om je toe te voegen aan de vakantie, dan verschijnt die hier.
                        @endif
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($vacations as $vacation)
                        <a
                            href="{{ route('vacations.show', $vacation) }}"
                            class="flex flex-col bg-white p-6 shadow-sm ring-1 ring-slate-200 rounded-2xl transition hover:ring-sky-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500"
                        >
                            <span class="inline-block w-max rounded-full px-3 py-1 text-xs font-semibold {{ $vacation->phase->badgeClasses() }}">
                                {{ $vacation->phase->label() }}
                            </span>

                            <p class="mt-4 text-lg font-bold text-slate-900">{{ $vacation->name }}</p>

                            {{-- Datum: definitief als die er is, anders het bereik dat gepeild wordt. --}}
                            @if ($vacation->final_start_date && $vacation->final_end_date)
                                <p class="mt-1 text-sm font-semibold text-sky-800">
                                    📅 {{ $vacation->final_start_date->locale('nl')->translatedFormat('j M') }}
                                    t/m {{ $vacation->final_end_date->locale('nl')->translatedFormat('j M Y') }}
                                </p>
                            @elseif ($vacation->planning_start_date && $vacation->planning_end_date)
                                <p class="mt-1 text-sm text-slate-600">
                                    📅 Datum nog niet geprikt
                                </p>
                            @else
                                <p class="mt-1 text-sm text-slate-600">📅 Nog geen datumbereik</p>
                            @endif

                            @if ($vacation->description)
                                <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $vacation->description }}</p>
                            @endif

                            <p class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-slate-600">
                                👥 {{ $vacation->users->count() }} {{ $vacation->users->count() === 1 ? 'deelnemer' : 'deelnemers' }}
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
