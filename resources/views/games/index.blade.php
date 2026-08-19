<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Drankspellen') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl">
                    <p class="text-3xl">🥃</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900">Picolo</p>
                </div>

                <div class="bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl">
                    <p class="text-3xl">💥</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900">Boom It</p>
                </div>

                <div class="bg-white p-6 shadow-sm ring-1 ring-slate-100 rounded-2xl">
                    <p class="text-3xl">🍻</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900">Broers</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
