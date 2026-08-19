@php
    $hotel = $hotel ?? null;
@endphp

<div>
    <x-input-label for="{{ $prefix }}_name" value="Naam hotel/accommodatie" />
    <x-text-input id="{{ $prefix }}_name" name="name" class="mt-1 block w-full" :value="old('name', $hotel->name ?? '')" required />
</div>

<div>
    <x-input-label for="{{ $prefix }}_url" value="Link (Airbnb, Booking.com, hotelsite)" />
    <x-text-input id="{{ $prefix }}_url" name="url" type="url" class="mt-1 block w-full" :value="old('url', $hotel->url ?? '')" placeholder="https://..." />
</div>

<div>
    <x-input-label for="{{ $prefix }}_image_url" value="Foto URL (optioneel)" />
    <x-text-input id="{{ $prefix }}_image_url" name="image_url" type="url" class="mt-1 block w-full" :value="old('image_url')" placeholder="https://... (we proberen 'm anders automatisch op te halen)" />
    <p class="mt-1 text-xs text-slate-400">Sommige sites (zoals Booking.com) blokkeren automatisch ophalen, plak dan hier zelf een link naar een foto.</p>
</div>

<div>
    <x-input-label value="Prijs overnachting" />
    <div class="mt-1.5 flex flex-wrap gap-4 text-sm text-slate-700">
        <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="price_accommodation_unit" value="total" x-model="accommodationUnit" class="text-sky-600 focus:ring-sky-500">
            Per nacht (totaal)
        </label>
        <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="price_accommodation_unit" value="per_person" x-model="accommodationUnit" class="text-sky-600 focus:ring-sky-500">
            Per nacht, per persoon
        </label>
    </div>
    <div class="relative mt-2">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">€</span>
        <x-text-input id="{{ $prefix }}_price_accommodation_per_night" name="price_accommodation_per_night" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('price_accommodation_per_night', $hotel->price_accommodation_per_night ?? '')" />
    </div>
</div>

<div>
    <x-input-label for="{{ $prefix }}_room_layout" value="Kamerindeling" />
    <textarea
        id="{{ $prefix }}_room_layout"
        name="room_layout"
        rows="2"
        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm"
        placeholder="Bijv. 2 kamers: 1x 2-persoonskamer, 1x 4-persoonskamer met stapelbedden"
    >{{ old('room_layout', $hotel->room_layout ?? '') }}</textarea>
</div>

<x-input-error class="mt-2" :messages="$errors->get('name')" />
<x-input-error class="mt-2" :messages="$errors->get('url')" />
<x-input-error class="mt-2" :messages="$errors->get('image_url')" />
<x-input-error class="mt-2" :messages="$errors->get('price_accommodation_per_night')" />
<x-input-error class="mt-2" :messages="$errors->get('room_layout')" />
