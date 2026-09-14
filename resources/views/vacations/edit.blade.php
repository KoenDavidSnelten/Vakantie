<x-app-layout :title="'Bewerken · '.$vacation->name">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                {{ __('Vakantie bewerken') }}
            </h2>
            <a href="{{ route('vacations.show', $vacation) }}" class="text-sm text-slate-600 hover:text-slate-900">
                {{ __('Bekijken') }} &rarr;
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-slate-100 sm:rounded-2xl">
                <form method="post" action="{{ route('vacations.update', $vacation) }}" class="space-y-6">
                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="name" :value="__('Naam')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $vacation->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Informatie')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-slate-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg shadow-sm">{{ old('description', $vacation->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div>
                        <x-input-label for="phase" :value="__('Fase')" />
                        <select id="phase" name="phase" class="mt-1 block w-full border-slate-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg shadow-sm">
                            @foreach (\App\Enums\VacationPhase::cases() as $phase)
                                <option value="{{ $phase->value }}" @selected(old('phase', $vacation->phase->value) === $phase->value)>
                                    {{ $phase->label() }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('phase')" />
                    </div>

                    <div>
                        <x-input-label :value="__('Deelnemers')" />
                        <div class="mt-2 space-y-2 max-h-64 overflow-y-auto border border-slate-200 rounded-lg p-3">
                            @forelse ($users as $user)
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="user_ids[]"
                                        value="{{ $user->id }}"
                                        class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500"
                                        @checked($vacation->users->contains('id', $user->id))
                                    >
                                    <span class="text-sm text-slate-700">{{ $user->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-slate-500">{{ __('Nog geen gebruikers geregistreerd.') }}</p>
                            @endforelse
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('user_ids')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button data-busy-label="Opslaan&hellip;">{{ __('Opslaan') }}</x-primary-button>
                        <a href="{{ route('vacations.show', $vacation) }}" class="rounded text-sm text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">{{ __('Annuleren') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
