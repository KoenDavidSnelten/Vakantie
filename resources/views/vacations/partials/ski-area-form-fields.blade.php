@php
    $skiArea = $skiArea ?? null;
@endphp

<div>
    <x-input-label for="{{ $prefix }}_name" value="Naam skigebied" />
    <x-text-input id="{{ $prefix }}_name" name="name" class="mt-1 block w-full" :value="old('name', $skiArea->name ?? '')" placeholder="Bijv. Kaprun / Zell am See" required />
</div>

<div>
    <x-input-label for="{{ $prefix }}_price_ski_pass" value="Prijs skipas 5 dagen (per persoon)" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">€</span>
        <x-text-input id="{{ $prefix }}_price_ski_pass" name="price_ski_pass" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('price_ski_pass', $skiArea->price_ski_pass ?? '')" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="{{ $prefix }}_distance_to_slopes_km" value="Afstand tot piste/lift (km)" />
        <x-text-input id="{{ $prefix }}_distance_to_slopes_km" name="distance_to_slopes_km" type="number" step="0.1" min="0" class="mt-1 block w-full" :value="old('distance_to_slopes_km', $skiArea->distance_to_slopes_km ?? '')" placeholder="Bijv. 1.5" />
    </div>
    <div class="flex items-end pb-2.5">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
            <input type="checkbox" name="has_bus" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked(old('has_bus', $skiArea->has_bus ?? false))>
            🚌 Er is een bus/shuttle naar het skigebied
        </label>
    </div>
</div>

<div>
    <x-input-label value="Kaart skigebied (optioneel)" />
    <div class="mt-1.5 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <input
                id="{{ $prefix }}_ski_area_map"
                name="ski_area_map"
                type="file"
                accept="image/*"
                class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
            >
            <p class="mt-1 text-xs text-slate-400">Upload een foto/screenshot van de pistekaart</p>
        </div>
        <div>
            <x-text-input id="{{ $prefix }}_ski_area_map_url" name="ski_area_map_url" type="url" class="mt-1 block w-full" :value="old('ski_area_map_url')" placeholder="https://... (of plak een link)" />
            <p class="mt-1 text-xs text-slate-400">Of plak een link naar een pistekaart</p>
        </div>
    </div>
    @if (($skiArea->ski_area_map_url ?? null))
        <p class="mt-1.5 text-xs text-slate-500">
            Huidige kaart: <a href="{{ $skiArea->ski_area_map_url }}" target="_blank" rel="noopener noreferrer" class="text-sky-700 hover:underline">bekijken</a> — upload of plak een nieuwe link om te vervangen.
        </p>
    @endif
    <x-input-error class="mt-2" :messages="$errors->get('ski_area_map')" />
    <x-input-error class="mt-2" :messages="$errors->get('ski_area_map_url')" />
</div>

<x-input-error class="mt-2" :messages="$errors->get('name')" />
<x-input-error class="mt-2" :messages="$errors->get('price_ski_pass')" />
<x-input-error class="mt-2" :messages="$errors->get('distance_to_slopes_km')" />
