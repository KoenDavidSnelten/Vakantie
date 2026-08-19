<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                {{ __('Vakanties') }}
            </h2>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('vacations.create') }}" class="inline-flex items-center px-5 py-2.5 bg-sky-600 rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-sky-500 transition">
                    {{ __('Nieuwe vakantie') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($vacations->isEmpty())
                <div class="rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
                    <p class="text-4xl">🧳</p>
                    <p class="mt-4 font-semibold text-slate-700">Nog geen vakanties</p>
                    <p class="mt-2 text-sm text-slate-500">
                        @if (auth()->user()->isAdmin())
                            Maak de eerste vakantie aan om te beginnen met plannen.
                        @else
                            Zodra je aan een vakantie wordt toegevoegd, verschijnt die hier.
                        @endif
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($vacations as $vacation)
                        <a href="{{ route('vacations.show', $vacation) }}" class="block bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl hover:ring-sky-200 transition">
                            <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $vacation->phase->badgeClasses() }}">
                                {{ $vacation->phase->label() }}
                            </span>
                            <p class="mt-4 text-lg font-semibold text-slate-900">{{ $vacation->name }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
