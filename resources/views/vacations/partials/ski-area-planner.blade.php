@php
    use App\Enums\PriceUnit;
    use App\Enums\VacationPhase;

    $isAdmin = auth()->user()->isAdmin();
    $isParticipant = $vacation->users->contains('id', auth()->id());
    $participantCount = $vacation->users->count();
    $canBookHotels = $vacation->phase === VacationPhase::Booking;
    $sortedSkiAreas = $vacation->skiAreas->sortByDesc(fn ($skiArea) => $skiArea->score());
@endphp

<div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-100 rounded-2xl">
    <h3 class="text-lg font-medium text-slate-900">📍 Locatieplanner</h3>
    <p class="mt-1 text-sm text-slate-500">Verzamel skigebieden en de hotels daarin (Airbnb, Booking.com, hotelsite), en stem op je favoriet.</p>

    <div class="mt-6 space-y-6">
        @forelse ($sortedSkiAreas as $skiArea)
            @php
                $skiAreaLikes = $skiArea->votes->where('value', 1)->count();
                $skiAreaDislikes = $skiArea->votes->where('value', -1)->count();
                $myVote = $skiArea->votes->firstWhere('user_id', auth()->id())?->value;
                $canEditSkiArea = $isAdmin || $skiArea->user_id === auth()->id();
                $sortedHotels = $skiArea->hotels->sortByDesc(fn ($hotel) => $hotel->score());
            @endphp
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4" x-data="{
                editing: {{ old('_editing_ski_area_id') == $skiArea->id ? 'true' : 'false' }},
                showComments: false,
            }">
                <div x-show="!editing" class="flex gap-4">
                    <div class="flex flex-col items-center gap-1.5">
                        <form method="post" action="{{ route('vacations.ski-areas.vote', [$vacation, $skiArea]) }}">
                            @csrf
                            <input type="hidden" name="value" value="1">
                            <button
                                type="submit"
                                {{ ($isAdmin || $isParticipant) ? '' : 'disabled' }}
                                class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold transition {{ $myVote === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-white text-slate-500 hover:bg-slate-100' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Vind ik leuk"
                            >
                                👍 {{ $skiAreaLikes }}
                            </button>
                        </form>
                        <form method="post" action="{{ route('vacations.ski-areas.vote', [$vacation, $skiArea]) }}">
                            @csrf
                            <input type="hidden" name="value" value="-1">
                            <button
                                type="submit"
                                {{ ($isAdmin || $isParticipant) ? '' : 'disabled' }}
                                class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold transition {{ $myVote === -1 ? 'bg-rose-100 text-rose-700' : 'bg-white text-slate-500 hover:bg-slate-100' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Vind ik niet leuk"
                            >
                                👎 {{ $skiAreaDislikes }}
                            </button>
                        </form>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 flex-wrap">
                            <div>
                                <p class="text-base font-bold text-slate-900">⛷️ {{ $skiArea->name }}</p>
                                <p class="text-xs text-slate-400">Toegevoegd door {{ $skiArea->addedBy->name }}</p>
                            </div>

                            @if ($canEditSkiArea)
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="editing = true" class="text-xs text-sky-600 hover:text-sky-800">Bewerken</button>
                                    <form method="post" action="{{ route('vacations.ski-areas.destroy', [$vacation, $skiArea]) }}" onsubmit="return confirm('Skigebied (en alle hotels erin) verwijderen?')">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-xs text-rose-500 hover:text-rose-700">Verwijderen</button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                            @if ($skiArea->price_ski_pass !== null)
                                <span>⛷️ €{{ number_format($skiArea->price_ski_pass, 2, ',', '.') }} skipas 5 dagen p.p.</span>
                            @endif
                            @if ($skiArea->distance_to_slopes_km !== null)
                                <span>🚶 {{ number_format($skiArea->distance_to_slopes_km, 1, ',', '.') }} km tot piste/lift</span>
                            @endif
                            @if ($skiArea->has_bus)
                                <span>🚌 Bus/shuttle aanwezig</span>
                            @endif
                        </div>

                        @if ($skiArea->ski_area_map_url)
                            <a href="{{ $skiArea->ski_area_map_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-2 text-sm text-sky-700 hover:underline">
                                @if ($skiArea->ski_area_map_path)
                                    <img src="{{ $skiArea->ski_area_map_url }}" alt="" loading="lazy" class="h-10 w-10 rounded object-cover ring-1 ring-slate-200">
                                @endif
                                🗺️ Pistekaart bekijken
                            </a>
                        @endif

                        <div class="mt-3 border-t border-slate-200 pt-3">
                            <button type="button" @click="showComments = !showComments" class="flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700">
                                💬 Reacties ({{ $skiArea->comments->count() }})
                                <span x-text="showComments ? '▲' : '▼'"></span>
                            </button>

                            <div x-show="showComments" x-cloak class="mt-2">
                                <div class="space-y-1.5">
                                    @forelse ($skiArea->comments as $comment)
                                        <div class="flex items-start justify-between gap-2 text-sm">
                                            <p class="text-slate-600">
                                                <span class="font-medium text-slate-800">{{ $comment->user->name }}:</span>
                                                {{ $comment->body }}
                                            </p>
                                            @if ($isAdmin || $comment->user_id === auth()->id())
                                                <form method="post" action="{{ route('vacations.ski-areas.comments.destroy', [$vacation, $skiArea, $comment]) }}" onsubmit="return confirm('Reactie verwijderen?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="flex-shrink-0 text-xs text-slate-400 hover:text-rose-600">Verwijderen</button>
                                                </form>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400">Nog geen reacties.</p>
                                    @endforelse
                                </div>

                                @if ($isAdmin || $isParticipant)
                                    <form method="post" action="{{ route('vacations.ski-areas.comments.store', [$vacation, $skiArea]) }}" class="mt-2 flex gap-2">
                                        @csrf
                                        <input
                                            type="text"
                                            name="body"
                                            placeholder="Voeg een reactie toe..."
                                            maxlength="2000"
                                            required
                                            class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500"
                                        >
                                        <button type="submit" class="text-sm font-medium text-sky-600 hover:text-sky-800">Plaatsen</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if ($canEditSkiArea)
                    <div x-show="editing" x-cloak>
                        <form
                            method="post"
                            action="{{ route('vacations.ski-areas.update', [$vacation, $skiArea]) }}"
                            enctype="multipart/form-data"
                            class="space-y-4"
                        >
                            @csrf
                            @method('patch')
                            <input type="hidden" name="_editing_ski_area_id" value="{{ $skiArea->id }}">

                            @include('vacations.partials.ski-area-form-fields', ['prefix' => 'ski_area_edit_'.$skiArea->id, 'skiArea' => $skiArea])

                            <div class="flex items-center gap-3">
                                <x-primary-button>Opslaan</x-primary-button>
                                <button type="button" @click="editing = false" class="text-sm text-slate-500 hover:text-slate-700">Annuleren</button>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="mt-4 space-y-3 sm:ml-10">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hotels in dit gebied</p>

                    @if (! $canBookHotels)
                        <p class="text-sm text-slate-400">🔒 Hotels kunnen worden toegevoegd zodra de boekfase begint.</p>
                    @else
                    @forelse ($sortedHotels as $hotel)
                        @php
                            $hotelLikes = $hotel->votes->where('value', 1)->count();
                            $hotelDislikes = $hotel->votes->where('value', -1)->count();
                            $myHotelVote = $hotel->votes->firstWhere('user_id', auth()->id())?->value;
                            $canEditHotel = $isAdmin || $hotel->user_id === auth()->id();
                            $totalPerNight = $hotel->totalPricePerNight($participantCount);
                        @endphp
                        <div class="rounded-xl border border-slate-100 bg-white p-4" x-data="{
                            editing: {{ old('_editing_hotel_id') == $hotel->id ? 'true' : 'false' }},
                            showComments: false,
                            accommodationUnit: '{{ $hotel->price_accommodation_unit->value ?? 'total' }}',
                        }">
                            <div x-show="!editing" class="flex gap-4">
                                @if ($hotel->image_url)
                                    <img
                                        src="{{ $hotel->image_url }}"
                                        alt=""
                                        loading="lazy"
                                        class="h-20 w-20 flex-shrink-0 rounded-lg bg-slate-100 object-cover"
                                        onerror="this.remove()"
                                    >
                                @endif

                                <div class="flex flex-col items-center gap-1.5">
                                    @if ($totalPerNight !== null)
                                        <div class="rounded-lg bg-sky-50 px-2 py-1 text-center" title="Overnachting + skipas (o.b.v. 5 dagen), per persoon per nacht">
                                            <p class="text-sm font-bold text-sky-800 whitespace-nowrap">€{{ number_format($totalPerNight, 2, ',', '.') }}</p>
                                            <p class="text-[10px] leading-tight text-sky-600">per nacht p.p.</p>
                                        </div>
                                    @endif
                                    <form method="post" action="{{ route('vacations.hotels.vote', [$vacation, $skiArea, $hotel]) }}">
                                        @csrf
                                        <input type="hidden" name="value" value="1">
                                        <button
                                            type="submit"
                                            {{ ($isAdmin || $isParticipant) ? '' : 'disabled' }}
                                            class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold transition {{ $myHotelVote === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                            title="Vind ik leuk"
                                        >
                                            👍 {{ $hotelLikes }}
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('vacations.hotels.vote', [$vacation, $skiArea, $hotel]) }}">
                                        @csrf
                                        <input type="hidden" name="value" value="-1">
                                        <button
                                            type="submit"
                                            {{ ($isAdmin || $isParticipant) ? '' : 'disabled' }}
                                            class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold transition {{ $myHotelVote === -1 ? 'bg-rose-100 text-rose-700' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                            title="Vind ik niet leuk"
                                        >
                                            👎 {{ $hotelDislikes }}
                                        </button>
                                    </form>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 flex-wrap">
                                        <div>
                                            @if ($hotel->url)
                                                <a href="{{ $hotel->url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-sky-700 hover:underline">
                                                    {{ $hotel->name }} ↗
                                                </a>
                                            @else
                                                <p class="font-semibold text-slate-900">{{ $hotel->name }}</p>
                                            @endif
                                            <p class="text-xs text-slate-400">Toegevoegd door {{ $hotel->addedBy->name }}</p>
                                        </div>

                                        @if ($canEditHotel)
                                            <div class="flex items-center gap-3">
                                                <button type="button" @click="editing = true" class="text-xs text-sky-600 hover:text-sky-800">Bewerken</button>
                                                <form method="post" action="{{ route('vacations.hotels.destroy', [$vacation, $skiArea, $hotel]) }}" onsubmit="return confirm('Hotel verwijderen?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="text-xs text-rose-500 hover:text-rose-700">Verwijderen</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($hotel->price_accommodation_per_night !== null)
                                        <p class="mt-2 text-sm text-slate-600">
                                            🏨 €{{ number_format($hotel->price_accommodation_per_night, 2, ',', '.') }} / nacht
                                            {{ $hotel->price_accommodation_unit === PriceUnit::PerPerson ? 'p.p.' : '' }}
                                        </p>
                                    @endif

                                    @if ($hotel->room_layout)
                                        <p class="mt-2 text-sm text-slate-600 whitespace-pre-line">🛏️ {{ $hotel->room_layout }}</p>
                                    @endif

                                    <div class="mt-3 border-t border-slate-100 pt-3">
                                        <button type="button" @click="showComments = !showComments" class="flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700">
                                            💬 Reacties ({{ $hotel->comments->count() }})
                                            <span x-text="showComments ? '▲' : '▼'"></span>
                                        </button>

                                        <div x-show="showComments" x-cloak class="mt-2">
                                            <div class="space-y-1.5">
                                                @forelse ($hotel->comments as $comment)
                                                    <div class="flex items-start justify-between gap-2 text-sm">
                                                        <p class="text-slate-600">
                                                            <span class="font-medium text-slate-800">{{ $comment->user->name }}:</span>
                                                            {{ $comment->body }}
                                                        </p>
                                                        @if ($isAdmin || $comment->user_id === auth()->id())
                                                            <form method="post" action="{{ route('vacations.hotels.comments.destroy', [$vacation, $skiArea, $hotel, $comment]) }}" onsubmit="return confirm('Reactie verwijderen?')">
                                                                @csrf
                                                                @method('delete')
                                                                <button type="submit" class="flex-shrink-0 text-xs text-slate-400 hover:text-rose-600">Verwijderen</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <p class="text-xs text-slate-400">Nog geen reacties.</p>
                                                @endforelse
                                            </div>

                                            @if ($isAdmin || $isParticipant)
                                                <form method="post" action="{{ route('vacations.hotels.comments.store', [$vacation, $skiArea, $hotel]) }}" class="mt-2 flex gap-2">
                                                    @csrf
                                                    <input
                                                        type="text"
                                                        name="body"
                                                        placeholder="Voeg een reactie toe..."
                                                        maxlength="2000"
                                                        required
                                                        class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500"
                                                    >
                                                    <button type="submit" class="text-sm font-medium text-sky-600 hover:text-sky-800">Plaatsen</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($canEditHotel)
                                <div x-show="editing" x-cloak>
                                    <form
                                        method="post"
                                        action="{{ route('vacations.hotels.update', [$vacation, $skiArea, $hotel]) }}"
                                        enctype="multipart/form-data"
                                        class="space-y-4"
                                    >
                                        @csrf
                                        @method('patch')
                                        <input type="hidden" name="_editing_hotel_id" value="{{ $hotel->id }}">

                                        @include('vacations.partials.hotel-form-fields', ['prefix' => 'hotel_edit_'.$hotel->id, 'hotel' => $hotel])

                                        <div class="flex items-center gap-3">
                                            <x-primary-button>Opslaan</x-primary-button>
                                            <button type="button" @click="editing = false" class="text-sm text-slate-500 hover:text-slate-700">Annuleren</button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Nog geen hotels toegevoegd in dit gebied.</p>
                    @endforelse

                    @if ($isAdmin || $isParticipant)
                        <details class="pt-1" {{ $errors->any() && old('_adding_hotel_to_ski_area_id') == $skiArea->id ? 'open' : '' }}>
                            <summary class="text-sm font-medium text-sky-600 cursor-pointer select-none">+ Hotel toevoegen aan {{ $skiArea->name }}</summary>

                            <form
                                method="post"
                                action="{{ route('vacations.hotels.store', [$vacation, $skiArea]) }}"
                                enctype="multipart/form-data"
                                class="mt-3 space-y-4"
                                x-data="{ accommodationUnit: 'total' }"
                            >
                                @csrf
                                <input type="hidden" name="_adding_hotel_to_ski_area_id" value="{{ $skiArea->id }}">

                                @include('vacations.partials.hotel-form-fields', ['prefix' => 'hotel_new_'.$skiArea->id, 'hotel' => null])

                                <x-primary-button>Hotel toevoegen</x-primary-button>
                            </form>
                        </details>
                    @endif
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">Nog geen skigebieden toegevoegd.</p>
        @endforelse
    </div>

    @if ($isAdmin || $isParticipant)
        <details class="mt-6 border-t border-slate-100 pt-6" {{ $errors->any() && ! old('_editing_ski_area_id') && ! old('_adding_hotel_to_ski_area_id') && ! old('_editing_hotel_id') ? 'open' : '' }}>
            <summary class="text-sm font-semibold text-slate-900 cursor-pointer select-none">
                + Skigebied toevoegen
            </summary>

            <form
                method="post"
                action="{{ route('vacations.ski-areas.store', $vacation) }}"
                enctype="multipart/form-data"
                class="mt-4 space-y-4"
            >
                @csrf

                @include('vacations.partials.ski-area-form-fields', ['prefix' => 'ski_area_new', 'skiArea' => null])

                <x-primary-button>Skigebied toevoegen</x-primary-button>
            </form>
        </details>
    @endif
</div>
