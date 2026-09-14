@php
    $vehicle = $vehicle ?? null;

    // Eigen foutenzak per formulier, zodat een fout niet onder alle auto's verschijnt.
    $bagErrors = $errors->getBag($bag);
    $failed = $bagErrors->any();
    $value = fn (string $key, $fallback = '') => $failed ? old($key, $fallback) : $fallback;
@endphp

<div>
    <x-input-label value="Soort" />
    <div class="mt-1.5 flex flex-wrap gap-4 text-sm text-slate-700">
        <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="type" value="own" x-model="vehicleType" class="text-sky-600 focus:ring-sky-500">
            Eigen auto
        </label>
        <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="type" value="rental" x-model="vehicleType" class="text-sky-600 focus:ring-sky-500">
            Huurauto
        </label>
    </div>
    <x-input-error class="mt-2" :messages="$bagErrors->get('type')" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="{{ $prefix }}_car_model" value="Soort auto" />
        <x-text-input id="{{ $prefix }}_car_model" name="car_model" class="mt-1 block w-full" :value="$value('car_model', $vehicle->car_model ?? '')" placeholder="Bijv. Ford Mondeo" required />
        <x-input-error class="mt-2" :messages="$bagErrors->get('car_model')" />
    </div>
    <div>
        <x-input-label for="{{ $prefix }}_seats" value="Zitplaatsen" />
        <x-text-input id="{{ $prefix }}_seats" name="seats" type="number" min="1" max="99" class="mt-1 block w-full" :value="$value('seats', $vehicle->seats ?? '')" />
        <p class="mt-1 text-xs text-slate-600">Inclusief de bestuurder &ndash; hiermee zie je hoeveel plekken er nog vrij zijn.</p>
        <x-input-error class="mt-2" :messages="$bagErrors->get('seats')" />
    </div>
</div>

<div x-show="vehicleType === 'rental'">
    <x-input-label for="{{ $prefix }}_price_per_day" value="Prijs per dag" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-600">€</span>
        <x-text-input id="{{ $prefix }}_price_per_day" name="price_per_day" type="number" step="0.01" min="0" class="block w-full pl-7" :value="$value('price_per_day', $vehicle->price_per_day ?? '')" />
    </div>
    <x-input-error class="mt-2" :messages="$bagErrors->get('price_per_day')" />
</div>

<div class="flex flex-wrap gap-4">
    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
        <input type="checkbox" name="has_winter_tires" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked($failed ? old('has_winter_tires') : ($vehicle->has_winter_tires ?? false))>
        ❄️ Winterbanden
    </label>
    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
        <input type="checkbox" name="has_large_trunk" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked($failed ? old('has_large_trunk') : ($vehicle->has_large_trunk ?? false))>
        🧳 Grote kofferbak
    </label>
    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
        <input type="checkbox" name="has_roof_box" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked($failed ? old('has_roof_box') : ($vehicle->has_roof_box ?? false))>
        📦 Dakkoffer
    </label>
</div>
