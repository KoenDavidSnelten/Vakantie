@php
    $vehicle = $vehicle ?? null;
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
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="{{ $prefix }}_car_model" value="Soort auto" />
        <x-text-input id="{{ $prefix }}_car_model" name="car_model" class="mt-1 block w-full" :value="old('car_model', $vehicle->car_model ?? '')" placeholder="Bijv. Ford Mondeo" required />
    </div>
    <div>
        <x-input-label for="{{ $prefix }}_seats" value="Zitplaatsen" />
        <x-text-input id="{{ $prefix }}_seats" name="seats" type="number" min="1" max="99" class="mt-1 block w-full" :value="old('seats', $vehicle->seats ?? '')" />
    </div>
</div>

<div x-show="vehicleType === 'rental'">
    <x-input-label for="{{ $prefix }}_price_per_day" value="Prijs per dag" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">€</span>
        <x-text-input id="{{ $prefix }}_price_per_day" name="price_per_day" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('price_per_day', $vehicle->price_per_day ?? '')" />
    </div>
</div>

<div class="flex flex-wrap gap-4">
    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
        <input type="checkbox" name="has_winter_tires" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked(old('has_winter_tires', $vehicle->has_winter_tires ?? false))>
        ❄️ Winterbanden
    </label>
    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
        <input type="checkbox" name="has_large_trunk" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked(old('has_large_trunk', $vehicle->has_large_trunk ?? false))>
        🧳 Grote kofferbak
    </label>
    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
        <input type="checkbox" name="has_roof_box" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" @checked(old('has_roof_box', $vehicle->has_roof_box ?? false))>
        📦 Dakkoffer
    </label>
</div>

<x-input-error class="mt-2" :messages="$errors->get('type')" />
<x-input-error class="mt-2" :messages="$errors->get('car_model')" />
<x-input-error class="mt-2" :messages="$errors->get('seats')" />
<x-input-error class="mt-2" :messages="$errors->get('price_per_day')" />
