@php
    $travelOption = $travelOption ?? null;

    // Eigen foutenzak per formulier, zodat een fout niet onder alle reisopties verschijnt.
    $bagErrors = $errors->getBag($bag);
    $failed = $bagErrors->any();
    $value = fn (string $key, $fallback = '') => $failed ? old($key, $fallback) : $fallback;

    // De bestemming kies je uit de skigebieden die al in de locatieplanner staan.
    $skiAreas = $vacation->skiAreas->sortBy('name');
    $selectedSkiArea = (string) $value('vacation_ski_area_id', $travelOption->vacation_ski_area_id ?? '');
@endphp

<div>
    <x-input-label for="{{ $prefix }}_vacation_ski_area_id" value="Bestemming" />

    @if ($skiAreas->isEmpty())
        <p class="mt-1 text-sm text-slate-600">
            Er staan nog geen skigebieden in de
            <a href="{{ route('vacations.locations.index', $vacation) }}" class="rounded font-semibold text-sky-700 hover:underline focus:outline-none focus:ring-2 focus:ring-sky-500">locatieplanner</a>.
            Voeg er daar één toe, dan kun je die hier als bestemming kiezen.
        </p>
        <input type="hidden" name="vacation_ski_area_id" value="">
    @else
        <select
            id="{{ $prefix }}_vacation_ski_area_id"
            name="vacation_ski_area_id"
            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">Nog niet bekend</option>
            @foreach ($skiAreas as $skiArea)
                <option value="{{ $skiArea->id }}" @selected($selectedSkiArea === (string) $skiArea->id)>
                    {{ $skiArea->name }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-600">Uit de skigebieden die in de locatieplanner staan.</p>
    @endif

    <x-input-error class="mt-2" :messages="$bagErrors->get('vacation_ski_area_id')" />
</div>

<div>
    <x-input-label for="{{ $prefix }}_name" value="Naam / omschrijving" />
    <x-text-input id="{{ $prefix }}_name" name="name" class="mt-1 block w-full" :value="$value('name', $travelOption->name ?? '')" placeholder="Bijv. Nachttrein Utrecht &ndash; Salzburg" required />
    <x-input-error class="mt-2" :messages="$bagErrors->get('name')" />
</div>

<div>
    <x-input-label for="{{ $prefix }}_url" value="Link (optioneel)" />
    <x-text-input id="{{ $prefix }}_url" name="url" type="url" class="mt-1 block w-full" :value="$value('url', $travelOption->url ?? '')" placeholder="https://..." />
    <x-input-error class="mt-2" :messages="$bagErrors->get('url')" />
</div>

<div>
    <x-input-label for="{{ $prefix }}_price_per_person" value="Prijs per persoon (optioneel)" />
    <div class="relative mt-1">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-600">€</span>
        <x-text-input id="{{ $prefix }}_price_per_person" name="price_per_person" type="number" step="0.01" min="0" class="block w-full pl-7" :value="$value('price_per_person', $travelOption->price_per_person ?? '')" />
    </div>
    <x-input-error class="mt-2" :messages="$bagErrors->get('price_per_person')" />
</div>

<div>
    <x-input-label for="{{ $prefix }}_notes" value="Opmerkingen (optioneel)" />
    <textarea
        id="{{ $prefix }}_notes"
        name="notes"
        rows="2"
        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 text-sm"
        placeholder="Bijv. vertrektijden, overstappen, etc."
    >{{ $value('notes', $travelOption->notes ?? '') }}</textarea>
    <x-input-error class="mt-2" :messages="$bagErrors->get('notes')" />
</div>
