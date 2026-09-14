@php
    use App\Enums\VacationPhase;

    $isAdmin = auth()->user()->isAdmin();
    $isParticipant = $vacation->users->contains('id', auth()->id());
    $canContribute = $isAdmin || $isParticipant;
    $isTravelPhase = $vacation->phase === VacationPhase::TravelPlanning;

    // Op bestemming gesorteerd, zodat opties naar hetzelfde skigebied bij elkaar staan.
    $sortedTravelOptions = $vacation->travelOptions
        ->sortBy(fn ($option) => [$option->skiArea?->name ?? 'zzz', $option->name]);
@endphp

<div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200 rounded-2xl">
    <h3 class="text-lg font-bold text-slate-900">🚆 Openbaar vervoer</h3>
    <p class="mt-1 text-sm text-slate-600">OV, bus of trein opties naar de locatie.</p>

    {{-- OV-opties kun je, net als auto's, in elke plannerfase verzamelen. In de
         reisplan-fase maak je de keuze definitief. --}}
    @unless ($isTravelPhase)
        <p class="mt-3 rounded-xl bg-slate-100 p-3 text-sm text-slate-700 ring-1 ring-slate-200">
            Alvast verzamelen mag: in de fase &ldquo;Reisplan&rdquo; kiezen we hieruit wat het wordt.
        </p>
    @endunless

    <div class="mt-6 space-y-3">
        @forelse ($sortedTravelOptions as $option)
            @php
                $canEdit = $isAdmin || $option->user_id === auth()->id();
                $editErrors = $errors->getBag('travelOption'.$option->id);
            @endphp
            <div class="rounded-xl border border-slate-200 p-4" x-data="{ editing: {{ $editErrors->any() ? 'true' : 'false' }} }">
                <div x-show="!editing" class="flex items-start justify-between gap-2 flex-wrap">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- De bestemming: een skigebied uit de locatieplanner. --}}
                            @if ($option->skiArea)
                                <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-900">
                                    <span aria-hidden="true">📍</span> {{ $option->skiArea->name }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                    <span aria-hidden="true">📍</span> Bestemming nog niet bekend
                                </span>
                            @endif

                            @if ($option->url)
                                <a href="{{ $option->url }}" target="_blank" rel="noopener noreferrer" class="rounded font-semibold text-sky-700 hover:underline focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    {{ $option->name }} <span aria-hidden="true">↗</span><span class="sr-only">(opent in een nieuw tabblad)</span>
                                </a>
                            @else
                                <p class="font-semibold text-slate-900">{{ $option->name }}</p>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600">Toegevoegd door {{ $option->addedBy->name }}</p>

                        @if ($option->price_per_person !== null)
                            <p class="mt-2 text-sm text-slate-700">💶 €{{ number_format($option->price_per_person, 2, ',', '.') }} p.p.</p>
                        @endif

                        @if ($option->notes)
                            <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $option->notes }}</p>
                        @endif
                    </div>

                    @if ($canEdit)
                        <div class="flex items-center gap-3">
                            <button type="button" @click="editing = true" class="rounded text-xs font-semibold text-sky-700 transition hover:text-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500">Bewerken</button>

                            <x-confirm-form
                                :action="route('vacations.travel-options.destroy', [$vacation, $option])"
                                title="Reisoptie verwijderen?"
                                :message="$option->name.' wordt definitief verwijderd.'"
                                class="rounded text-xs font-semibold text-rose-600 transition hover:text-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500"
                            >Verwijderen</x-confirm-form>
                        </div>
                    @endif
                </div>

                @if ($canEdit)
                    <div x-show="editing" x-cloak>
                        <form method="post" action="{{ route('vacations.travel-options.update', [$vacation, $option]) }}" class="space-y-4">
                            @csrf
                            @method('patch')

                            @include('vacations.partials.travel-option-form-fields', [
                                'prefix' => 'travel_option_edit_'.$option->id,
                                'travelOption' => $option,
                                'bag' => 'travelOption'.$option->id,
                            ])

                            <div class="flex items-center gap-3">
                                <x-primary-button data-busy-label="Opslaan&hellip;">Opslaan</x-primary-button>
                                <button type="button" @click="editing = false" class="rounded text-sm text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">Annuleren</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-slate-600">Nog geen opties toegevoegd.</p>
        @endforelse
    </div>

    @if ($canContribute)
        <details class="mt-6 border-t border-slate-100 pt-6" {{ $errors->getBag('travelOptionNew')->any() ? 'open' : '' }}>
            <summary class="text-sm font-semibold text-slate-900 cursor-pointer select-none">
                + Optie toevoegen
            </summary>

            <form method="post" action="{{ route('vacations.travel-options.store', $vacation) }}" class="mt-4 space-y-4">
                @csrf

                @include('vacations.partials.travel-option-form-fields', [
                    'prefix' => 'travel_option_new',
                    'travelOption' => null,
                    'bag' => 'travelOptionNew',
                ])

                <x-primary-button data-busy-label="Bezig&hellip;">Optie toevoegen</x-primary-button>
            </form>
        </details>
    @endif
</div>
