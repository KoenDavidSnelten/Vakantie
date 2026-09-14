<x-app-layout :title="'Reisplanner · '.$vacation->name">
    <x-slot name="header">
        <div class="space-y-3">
            <a href="{{ route('vacations.show', $vacation) }}" class="inline-block rounded text-sm text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">
                &larr; {{ $vacation->name }}
            </a>

            <div class="flex items-center justify-between flex-wrap gap-3">
                <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                    {{ __('Reisplanner') }}
                </h2>

                @include('vacations.partials.planner-nav')
            </div>

            @include('vacations.partials.phase-stepper')
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @include('vacations.partials.vehicle-planner')

            @include('vacations.partials.travel-option-planner')
        </div>
    </div>
</x-app-layout>
