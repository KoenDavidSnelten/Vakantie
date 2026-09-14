@php
    use App\Enums\VehicleType;

    $isAdmin = auth()->user()->isAdmin();
    $isParticipant = $vacation->users->contains('id', auth()->id());
    $sortedVehicles = $vacation->vehicles->sortBy('car_model');
    $withoutVehicle = $vacation->participantsWithoutVehicle();
    $canContribute = $isAdmin || $isParticipant;
@endphp

<div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200 rounded-2xl">
    <h3 class="text-lg font-bold text-slate-900">🚗 Auto's en wie rijdt met wie</h3>
    <p class="mt-1 text-sm text-slate-600">Geef aan welke auto's beschikbaar zijn (of voeg een huurauto-optie toe) en deel jezelf in bij een auto.</p>

    {{-- Wie zit nog nergens in? Bovenaan, want dat is het openstaande werk. --}}
    @if ($sortedVehicles->isNotEmpty() && $withoutVehicle->isNotEmpty())
        <div class="mt-4 rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200">
            <p class="text-sm font-semibold text-amber-900">
                Nog geen plek in een auto ({{ $withoutVehicle->count() }})
            </p>
            <div class="mt-1.5 flex flex-wrap gap-1">
                @foreach ($withoutVehicle as $person)
                    <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-amber-900 ring-1 ring-amber-300">{{ $person->name }}</span>
                @endforeach
            </div>
        </div>
    @elseif ($sortedVehicles->isNotEmpty())
        <p class="mt-4 rounded-xl bg-emerald-50 p-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-200">
            ✓ Iedereen heeft een plek in een auto.
        </p>
    @endif

    <div class="mt-6 space-y-3">
        @forelse ($sortedVehicles as $vehicle)
            @php
                $canEdit = $isAdmin || $vehicle->user_id === auth()->id();
                $canSeat = $isAdmin || $vehicle->user_id === auth()->id();
                $editErrors = $errors->getBag('vehicle'.$vehicle->id);
                $seatErrors = $errors->getBag('passengers'.$vehicle->id);
                $iAmAboard = $vehicle->passengers->contains('id', auth()->id());
                $seatsLeft = $vehicle->seatsLeft();
                $assignable = $vacation->users->reject(fn ($user) => $vehicle->passengers->contains('id', $user->id));
            @endphp
            <div class="rounded-xl border border-slate-200 p-4" x-data="{
                editing: {{ $editErrors->any() ? 'true' : 'false' }},
                vehicleType: '{{ $vehicle->type->value }}',
            }">
                <div x-show="!editing">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $vehicle->type === VehicleType::Rental ? 'bg-violet-100 text-violet-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $vehicle->type->label() }}
                                </span>
                                <p class="font-semibold text-slate-900">{{ $vehicle->car_model }}</p>

                                @if ($seatsLeft === 0)
                                    <span class="inline-block rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800">Vol</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600">Toegevoegd door {{ $vehicle->addedBy->name }}</p>

                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-700">
                                @if ($vehicle->seats !== null)
                                    <span>💺 {{ $vehicle->passengers->count() }} van de {{ $vehicle->seats }} plekken bezet</span>
                                @else
                                    <span>💺 {{ $vehicle->passengers->count() }} ingedeeld (aantal zitplaatsen onbekend)</span>
                                @endif
                                @if ($vehicle->type === VehicleType::Rental && $vehicle->price_per_day !== null)
                                    <span>💶 €{{ number_format($vehicle->price_per_day, 2, ',', '.') }} / dag</span>
                                @endif
                            </div>

                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                                <span>{{ $vehicle->has_winter_tires ? '❄️ Winterbanden' : 'Geen winterbanden' }}</span>
                                @if ($vehicle->has_large_trunk)
                                    <span>🧳 Grote kofferbak</span>
                                @endif
                                @if ($vehicle->has_roof_box)
                                    <span>📦 Dakkoffer</span>
                                @endif
                            </div>
                        </div>

                        @if ($canEdit)
                            <div class="flex items-center gap-3">
                                <button type="button" @click="editing = true" class="rounded text-xs font-semibold text-sky-700 transition hover:text-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500">Bewerken</button>

                                <x-confirm-form
                                    :action="route('vacations.vehicles.destroy', [$vacation, $vehicle])"
                                    title="Auto verwijderen?"
                                    :message="$vehicle->car_model.' verdwijnt uit de reisplanner. Wie erin was ingedeeld heeft daarna geen plek meer.'"
                                    class="rounded text-xs font-semibold text-rose-600 transition hover:text-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500"
                                >Verwijderen</x-confirm-form>
                            </div>
                        @endif
                    </div>

                    {{-- Wie rijdt er mee --}}
                    <div class="mt-3 border-t border-slate-100 pt-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-600">Rijdt mee</p>

                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                            @forelse ($vehicle->passengers as $passenger)
                                {{-- Een <div>, geen <span>: er zit een formulier in en dat mag
                                     niet binnen een phrasing-element staan. --}}
                                <div class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-800 ring-1 ring-slate-200">
                                    {{ $passenger->name }}

                                    @if ($canSeat || $passenger->id === auth()->id())
                                        <form method="post" action="{{ route('vacations.vehicles.passengers.destroy', [$vacation, $vehicle, $passenger->id]) }}">
                                            @csrf
                                            @method('delete')
                                            <button
                                                type="submit"
                                                class="rounded-full leading-none text-slate-500 transition hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500"
                                                aria-label="{{ $passenger->id === auth()->id() ? 'Jezelf' : $passenger->name }} uit {{ $vehicle->car_model }} halen"
                                                title="Uit deze auto halen"
                                            >✕</button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-slate-600">Nog niemand ingedeeld.</p>
                            @endforelse
                        </div>

                        @if ($canContribute)
                            <div class="mt-3 flex flex-wrap items-end gap-3">
                                {{-- Jezelf indelen kan altijd, met één klik. --}}
                                @if ($isParticipant && ! $iAmAboard && $seatsLeft !== 0)
                                    <form method="post" action="{{ route('vacations.vehicles.passengers.store', [$vacation, $vehicle]) }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                        <button type="submit" data-busy-label="Bezig&hellip;" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                                            Ik rij hierin mee
                                        </button>
                                    </form>
                                @endif

                                {{-- De eigenaar van de auto en de beheerder mogen ook anderen indelen. --}}
                                @if ($canSeat && $assignable->isNotEmpty() && $seatsLeft !== 0)
                                    <form method="post" action="{{ route('vacations.vehicles.passengers.store', [$vacation, $vehicle]) }}" class="flex items-end gap-2">
                                        @csrf
                                        <div>
                                            <label for="passenger_{{ $vehicle->id }}" class="block text-xs font-bold uppercase tracking-wide text-slate-600">Iemand toevoegen</label>
                                            <select id="passenger_{{ $vehicle->id }}" name="user_id" class="mt-1 rounded-lg border-slate-300 py-1.5 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                                                @php $free = $assignable->filter(fn ($user) => $withoutVehicle->contains('id', $user->id)); @endphp
                                                @php $taken = $assignable->reject(fn ($user) => $withoutVehicle->contains('id', $user->id)); @endphp

                                                @if ($free->isNotEmpty())
                                                    <optgroup label="Nog geen plek">
                                                        @foreach ($free as $person)
                                                            <option value="{{ $person->id }}">{{ $person->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif

                                                @if ($taken->isNotEmpty())
                                                    <optgroup label="Zit al in een andere auto">
                                                        @foreach ($taken as $person)
                                                            <option value="{{ $person->id }}">{{ $person->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            </select>
                                        </div>
                                        <button type="submit" data-busy-label="Bezig&hellip;" class="rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-slate-700 ring-1 ring-slate-300 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                            Toevoegen
                                        </button>
                                    </form>
                                @endif

                                @if ($seatsLeft === 0)
                                    <p class="text-xs font-semibold text-slate-600">Deze auto zit vol.</p>
                                @endif
                            </div>

                            <x-input-error class="mt-2" :messages="$seatErrors->get('user_id')" />
                        @endif
                    </div>
                </div>

                @if ($canEdit)
                    <div x-show="editing" x-cloak>
                        <form method="post" action="{{ route('vacations.vehicles.update', [$vacation, $vehicle]) }}" class="space-y-4">
                            @csrf
                            @method('patch')

                            @include('vacations.partials.vehicle-form-fields', [
                                'prefix' => 'vehicle_edit_'.$vehicle->id,
                                'vehicle' => $vehicle,
                                'bag' => 'vehicle'.$vehicle->id,
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
            <p class="text-sm text-slate-600">Nog geen auto's toegevoegd. Voeg er één toe om de indeling te kunnen maken.</p>
        @endforelse
    </div>

    @if ($canContribute)
        <details class="mt-6 border-t border-slate-100 pt-6" {{ $errors->getBag('vehicleNew')->any() ? 'open' : '' }}>
            <summary class="text-sm font-semibold text-slate-900 cursor-pointer select-none">
                + Auto toevoegen
            </summary>

            <form
                method="post"
                action="{{ route('vacations.vehicles.store', $vacation) }}"
                class="mt-4 space-y-4"
                x-data="{ vehicleType: 'own' }"
            >
                @csrf

                @include('vacations.partials.vehicle-form-fields', [
                    'prefix' => 'vehicle_new',
                    'vehicle' => null,
                    'bag' => 'vehicleNew',
                ])

                <x-primary-button data-busy-label="Bezig&hellip;">Auto toevoegen</x-primary-button>
            </form>
        </details>
    @endif
</div>
