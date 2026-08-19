@php
    use App\Enums\VacationPhase;

    $isAdmin = auth()->user()->isAdmin();
    $isParticipant = $vacation->users->contains('id', auth()->id());
    $canAddTravelOptions = $vacation->phase === VacationPhase::TravelPlanning;
    $sortedTravelOptions = $vacation->travelOptions->sortBy('name');
@endphp

<div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-100 rounded-2xl">
    <h3 class="text-lg font-medium text-slate-900">🚆 Openbaar vervoer</h3>
    <p class="mt-1 text-sm text-slate-500">OV, bus of trein opties naar de locatie.</p>

    @if (! $canAddTravelOptions)
        <p class="mt-4 text-sm text-slate-400">🔒 OV/bus/trein-opties kunnen worden toegevoegd zodra de reisplan-fase begint.</p>
    @else
        <div class="mt-6 space-y-3">
            @forelse ($sortedTravelOptions as $option)
                @php $canEdit = $isAdmin || $option->user_id === auth()->id(); @endphp
                <div class="rounded-xl border border-slate-100 p-4" x-data="{ editing: {{ old('_editing_travel_option_id') == $option->id ? 'true' : 'false' }} }">
                    <div x-show="!editing" class="flex items-start justify-between gap-2 flex-wrap">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span>{{ $option->type->icon() }}</span>
                                @if ($option->url)
                                    <a href="{{ $option->url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-sky-700 hover:underline">{{ $option->name }} ↗</a>
                                @else
                                    <p class="font-semibold text-slate-900">{{ $option->name }}</p>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400">Toegevoegd door {{ $option->addedBy->name }}</p>

                            @if ($option->price_per_person !== null)
                                <p class="mt-2 text-sm text-slate-600">💶 €{{ number_format($option->price_per_person, 2, ',', '.') }} p.p.</p>
                            @endif

                            @if ($option->notes)
                                <p class="mt-2 text-sm text-slate-600 whitespace-pre-line">{{ $option->notes }}</p>
                            @endif
                        </div>

                        @if ($canEdit)
                            <div class="flex items-center gap-3">
                                <button type="button" @click="editing = true" class="text-xs text-sky-600 hover:text-sky-800">Bewerken</button>
                                <form method="post" action="{{ route('vacations.travel-options.destroy', [$vacation, $option]) }}" onsubmit="return confirm('Optie verwijderen?')">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="text-xs text-rose-500 hover:text-rose-700">Verwijderen</button>
                                </form>
                            </div>
                        @endif
                    </div>

                    @if ($canEdit)
                        <div x-show="editing" x-cloak>
                            <form method="post" action="{{ route('vacations.travel-options.update', [$vacation, $option]) }}" class="space-y-4">
                                @csrf
                                @method('patch')
                                <input type="hidden" name="_editing_travel_option_id" value="{{ $option->id }}">

                                @include('vacations.partials.travel-option-form-fields', ['prefix' => 'travel_option_edit_'.$option->id, 'travelOption' => $option])

                                <div class="flex items-center gap-3">
                                    <x-primary-button>Opslaan</x-primary-button>
                                    <button type="button" @click="editing = false" class="text-sm text-slate-500 hover:text-slate-700">Annuleren</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">Nog geen opties toegevoegd.</p>
            @endforelse
        </div>

        @if ($isAdmin || $isParticipant)
            <details class="mt-6 border-t border-slate-100 pt-6" {{ $errors->any() && ! old('_editing_travel_option_id') ? 'open' : '' }}>
                <summary class="text-sm font-semibold text-slate-900 cursor-pointer select-none">
                    + Optie toevoegen
                </summary>

                <form method="post" action="{{ route('vacations.travel-options.store', $vacation) }}" class="mt-4 space-y-4">
                    @csrf

                    @include('vacations.partials.travel-option-form-fields', ['prefix' => 'travel_option_new', 'travelOption' => null])

                    <x-primary-button>Optie toevoegen</x-primary-button>
                </form>
            </details>
        @endif
    @endif
</div>
