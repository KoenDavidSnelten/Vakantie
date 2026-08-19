<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <a href="{{ route('vacations.show', $vacation) }}" class="text-sm text-slate-500 hover:text-slate-700">
                &larr; {{ $vacation->name }}
            </a>

            <div class="flex items-center justify-between flex-wrap gap-3">
                <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                    {{ __('Locatieplanner') }}
                </h2>

                @include('vacations.partials.planner-nav')
            </div>

            @include('vacations.partials.phase-stepper')
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('vacations.partials.ski-area-planner')
        </div>
    </div>
</x-app-layout>
