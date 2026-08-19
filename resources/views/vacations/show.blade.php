<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                    {{ $vacation->name }}
                </h2>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('vacations.edit', $vacation) }}" class="text-sm text-slate-600 hover:text-slate-900">
                        {{ __('Bewerken') }}
                    </a>
                @endif
            </div>

            @include('vacations.partials.phase-stepper')
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($vacation->phase->hasPlannerAccess())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="{{ route('vacations.date-planner', $vacation) }}" class="block bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl hover:ring-sky-300 hover:shadow-md transition">
                        <p class="text-3xl">📅</p>
                        <p class="mt-4 text-lg font-semibold text-slate-900">Datumplanner</p>
                    </a>

                    <a href="{{ route('vacations.locations.index', $vacation) }}" class="block bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl hover:ring-sky-300 hover:shadow-md transition">
                        <p class="text-3xl">📍</p>
                        <p class="mt-4 text-lg font-semibold text-slate-900">Locatieplanner</p>
                    </a>

                    <a href="{{ route('vacations.travel-planner', $vacation) }}" class="block bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl hover:ring-sky-300 hover:shadow-md transition">
                        <p class="text-3xl">🚗</p>
                        <p class="mt-4 text-lg font-semibold text-slate-900">Reisplanner</p>
                    </a>

                    <a href="{{ route('vacations.packing-list', $vacation) }}" class="block bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl hover:ring-sky-300 hover:shadow-md transition">
                        <p class="text-3xl">🧳</p>
                        <p class="mt-4 text-lg font-semibold text-slate-900">Paklijst</p>
                    </a>
                </div>
            @else
                <div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-100 rounded-2xl">
                    <h3 class="text-lg font-medium text-slate-900">{{ __('Informatie') }}</h3>

                    @if ($vacation->final_start_date && $vacation->final_end_date)
                        <p class="mt-2 text-sm font-semibold text-sky-700">
                            📅 {{ $vacation->final_start_date->format('d-m-Y') }} t/m {{ $vacation->final_end_date->format('d-m-Y') }}
                        </p>
                    @endif

                    <p class="mt-2 text-slate-600 whitespace-pre-line">
                        {{ $vacation->description ?: __('Nog geen informatie toegevoegd.') }}
                    </p>
                </div>

                <a href="{{ route('vacations.packing-list', $vacation) }}" class="block bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl hover:ring-sky-300 hover:shadow-md transition">
                    <p class="text-3xl">🧳</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900">Paklijst</p>
                </a>
            @endif
        </div>
    </div>
</x-app-layout>
