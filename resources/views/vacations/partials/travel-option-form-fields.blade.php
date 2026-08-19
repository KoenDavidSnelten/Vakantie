@php
    $travelOption = $travelOption ?? null;
@endphp

<div>
    <x-input-label value="Type" />
    <div class="mt-1.5 flex flex-wrap gap-4 text-sm text-slate-700">
        @foreach (\App\Enums\TravelOptionType::cases() as $option)
            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input
                    type="radio"
                    name="type"
                    value="{{ $option->value }}"
                    class="text-sky-600 focus:ring-sky-500"
                    @checked(old('type', $travelOption->type->value ?? 'trein') === $option->value)
                >
                {{ $option->icon() }} {{ $option->label() }}
            </label>
        @endforeach
    </div>
</div>

<div>
    <x-input-label for="{{ $prefix }}_name" value="Naam / omschrijving" />
    <x-text-input id="{{ $prefix }}_name" name="name" class="mt-1 block w-full" :value="old('name', $travelOption->name ?? '')" placeholder="Bijv. NS retour Salzburg" required />
</div>

<div>
    <x-input-label for="{{ $prefix }}_url" value="Link (optioneel)" />
    <x-text-input id="{{ $prefix }}_url" name="url" type="url" class="mt-1 block w-full" :value="old('url', $travelOption->url ?? '')" placeholder="https://..." />
</div>

<div>
    <x-input-label for="{{ $prefix }}_price_per_person" value="Prijs per persoon (optioneel)" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">€</span>
        <x-text-input id="{{ $prefix }}_price_per_person" name="price_per_person" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('price_per_person', $travelOption->price_per_person ?? '')" />
    </div>
</div>

<div>
    <x-input-label for="{{ $prefix }}_notes" value="Opmerkingen (optioneel)" />
    <textarea
        id="{{ $prefix }}_notes"
        name="notes"
        rows="2"
        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm"
        placeholder="Bijv. vertrektijden, overstappen, etc."
    >{{ old('notes', $travelOption->notes ?? '') }}</textarea>
</div>

<x-input-error class="mt-2" :messages="$errors->get('type')" />
<x-input-error class="mt-2" :messages="$errors->get('name')" />
<x-input-error class="mt-2" :messages="$errors->get('url')" />
<x-input-error class="mt-2" :messages="$errors->get('price_per_person')" />
<x-input-error class="mt-2" :messages="$errors->get('notes')" />
