@php
    use App\Enums\VehicleType;

    $isAdmin = auth()->user()->isAdmin();
    $isParticipant = $vacation->users->contains('id', auth()->id());
    $sortedVehicles = $vacation->vehicles->sortBy('car_model');
@endphp

<div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-100 rounded-2xl">
    <h3 class="text-lg font-medium text-slate-900">🚗 Auto's</h3>
    <p class="mt-1 text-sm text-slate-500">Geef aan welke auto's beschikbaar zijn, of voeg een huurauto-optie toe.</p>

    <div class="mt-6 space-y-3">
        @forelse ($sortedVehicles as $vehicle)
            @php $canEdit = $isAdmin || $vehicle->user_id === auth()->id(); @endphp
            <div class="rounded-xl border border-slate-100 p-4" x-data="{
                editing: {{ old('_editing_vehicle_id') == $vehicle->id ? 'true' : 'false' }},
                vehicleType: '{{ $vehicle->type->value }}',
            }">
                <div x-show="!editing" class="flex items-start justify-between gap-2 flex-wrap">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $vehicle->type === VehicleType::Rental ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $vehicle->type->label() }}
                            </span>
                            <p class="font-semibold text-slate-900">{{ $vehicle->car_model }}</p>
                        </div>
                        <p class="text-xs text-slate-400">Toegevoegd door {{ $vehicle->addedBy->name }}</p>

                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                            @if ($vehicle->seats !== null)
                                <span>💺 {{ $vehicle->seats }} zitplaatsen</span>
                            @endif
                            @if ($vehicle->type === VehicleType::Rental && $vehicle->price_per_day !== null)
                                <span>💶 €{{ number_format($vehicle->price_per_day, 2, ',', '.') }} / dag</span>
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
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
                            <button type="button" @click="editing = true" class="text-xs text-sky-600 hover:text-sky-800">Bewerken</button>
                            <form method="post" action="{{ route('vacations.vehicles.destroy', [$vacation, $vehicle]) }}" onsubmit="return confirm('Auto verwijderen?')">
                                @csrf
                                @method('delete')
                                <button type="submit" class="text-xs text-rose-500 hover:text-rose-700">Verwijderen</button>
                            </form>
                        </div>
                    @endif
                </div>

                @if ($canEdit)
                    <div x-show="editing" x-cloak>
                        <form method="post" action="{{ route('vacations.vehicles.update', [$vacation, $vehicle]) }}" class="space-y-4">
                            @csrf
                            @method('patch')
                            <input type="hidden" name="_editing_vehicle_id" value="{{ $vehicle->id }}">

                            @include('vacations.partials.vehicle-form-fields', ['prefix' => 'vehicle_edit_'.$vehicle->id, 'vehicle' => $vehicle])

                            <div class="flex items-center gap-3">
                                <x-primary-button>Opslaan</x-primary-button>
                                <button type="button" @click="editing = false" class="text-sm text-slate-500 hover:text-slate-700">Annuleren</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-slate-500">Nog geen auto's toegevoegd.</p>
        @endforelse
    </div>

    @if ($isAdmin || $isParticipant)
        <details class="mt-6 border-t border-slate-100 pt-6" {{ $errors->any() && ! old('_editing_vehicle_id') ? 'open' : '' }}>
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

                @include('vacations.partials.vehicle-form-fields', ['prefix' => 'vehicle_new', 'vehicle' => null])

                <x-primary-button>Auto toevoegen</x-primary-button>
            </form>
        </details>
    @endif
</div>
