@php
    $skiArea = $skiArea ?? null;

    // Eigen foutenzak per formulier: anders verschijnt een fout bij één skigebied
    // onder alle andere formulieren op de pagina.
    $bagErrors = $errors->getBag($bag);
    $failed = $bagErrors->any();
    $value = fn (string $key, $fallback = '') => $failed ? old($key, $fallback) : $fallback;
@endphp

<div>
    <x-input-label for="{{ $prefix }}_name" value="Naam skigebied" />
    <x-text-input id="{{ $prefix }}_name" name="name" class="mt-1 block w-full" :value="$value('name', $skiArea->name ?? '')" placeholder="Bijv. Kaprun / Zell am See" required />
    <x-input-error class="mt-2" :messages="$bagErrors->get('name')" />
</div>

<div>
    <x-input-label for="{{ $prefix }}_price_ski_pass" value="Prijs skipas 5 dagen (per persoon)" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-600">€</span>
        <x-text-input id="{{ $prefix }}_price_ski_pass" name="price_ski_pass" type="number" step="0.01" min="0" class="block w-full pl-7" :value="$value('price_ski_pass', $skiArea->price_ski_pass ?? '')" />
    </div>
    <x-input-error class="mt-2" :messages="$bagErrors->get('price_ski_pass')" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="{{ $prefix }}_distance_to_slopes_km" value="Afstand tot piste/lift (km)" />
        <x-text-input id="{{ $prefix }}_distance_to_slopes_km" name="distance_to_slopes_km" type="number" step="0.1" min="0" class="mt-1 block w-full" :value="$value('distance_to_slopes_km', $skiArea->distance_to_slopes_km ?? '')" placeholder="Bijv. 1.5" />
        <x-input-error class="mt-2" :messages="$bagErrors->get('distance_to_slopes_km')" />
    </div>
    <div class="flex items-end pb-2.5">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
            <input type="checkbox" name="has_bus" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked($failed ? old('has_bus') : ($skiArea->has_bus ?? false))>
            🚌 Er is een bus/shuttle naar het skigebied
        </label>
    </div>
</div>

<div>
    <x-input-label for="{{ $prefix }}_ski_area_map_url" value="Link naar pistekaart (optioneel)" />
    <x-text-input id="{{ $prefix }}_ski_area_map_url" name="ski_area_map_url" type="url" class="mt-1 block w-full" :value="$value('ski_area_map_url', $skiArea->ski_area_map_url ?? '')" placeholder="https://..." />
    <p class="mt-1 text-xs text-slate-600">Plak een link naar de pistekaart van het skigebied. Leeg laten verwijdert de link.</p>
    <x-input-error class="mt-2" :messages="$bagErrors->get('ski_area_map_url')" />
</div>
