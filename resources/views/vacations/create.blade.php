<x-app-layout title="Nieuwe vakantie">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Nieuwe vakantie') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-slate-100 sm:rounded-2xl">
                <form method="post" action="{{ route('vacations.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Naam')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Informatie')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-slate-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Aanmaken') }}</x-primary-button>
                        <a href="{{ route('vacations.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Annuleren') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
