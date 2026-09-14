@php
    use App\Enums\PriceUnit;
    use App\Enums\VacationPhase;

    $isAdmin = auth()->user()->isAdmin();
    $isParticipant = $vacation->users->contains('id', auth()->id());
    $participantCount = $vacation->users->count();
    $isBookingPhase = $vacation->phase === VacationPhase::Booking;
    $sortedSkiAreas = $vacation->skiAreas->sortByDesc(fn ($skiArea) => $skiArea->score());
    $canContribute = $isAdmin || $isParticipant;

    // Namen van de stemmers, zodat je niet alleen een teller ziet maar ook wie.
    $voterNames = fn ($votes, int $value) => $votes
        ->where('value', $value)
        ->map(fn ($vote) => $vote->user?->name)
        ->filter()
        ->sort()
        ->values();
@endphp

<div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200 rounded-2xl">
    <h3 class="text-lg font-bold text-slate-900">📍 Locatieplanner</h3>
    <p class="mt-1 text-sm text-slate-600">Verzamel skigebieden en de hotels daarin (Airbnb, Booking.com, hotelsite), en stem op je favoriet.</p>

    <div class="mt-6 space-y-6">
        @forelse ($sortedSkiAreas as $skiArea)
            @php
                $skiAreaLikers = $voterNames($skiArea->votes, 1);
                $skiAreaDislikers = $voterNames($skiArea->votes, -1);
                $myVote = $skiArea->votes->firstWhere('user_id', auth()->id())?->value;
                $canEditSkiArea = $isAdmin || $skiArea->user_id === auth()->id();
                $sortedHotels = $skiArea->hotels->sortByDesc(fn ($hotel) => $hotel->score());
                $skiAreaEditErrors = $errors->getBag('skiArea'.$skiArea->id);
                $skiAreaCommentErrors = $errors->getBag('skiAreaComment'.$skiArea->id);
                $newHotelErrors = $errors->getBag('hotelNew'.$skiArea->id);
            @endphp
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4" x-data="{
                editing: {{ $skiAreaEditErrors->any() ? 'true' : 'false' }},
                showComments: {{ $skiAreaCommentErrors->any() ? 'true' : 'false' }},
                showVoters: false,
            }">
                <div x-show="!editing" class="flex gap-4">
                    <div class="flex flex-col items-center gap-1.5">
                        <form method="post" action="{{ route('vacations.ski-areas.vote', [$vacation, $skiArea]) }}">
                            @csrf
                            <input type="hidden" name="value" value="1">
                            <button
                                type="submit"
                                {{ $canContribute ? '' : 'disabled' }}
                                class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-sky-500 {{ $myVote === 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-white text-slate-600 hover:bg-slate-100' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                aria-label="{{ $myVote === 1 ? 'Je stem intrekken voor' : 'Stem voor' }} {{ $skiArea->name }}"
                                title="{{ $myVote === 1 ? 'Je stem intrekken' : 'Vind ik leuk' }}"
                            >
                                <span aria-hidden="true">👍</span> {{ $skiAreaLikers->count() }}
                            </button>
                        </form>
                        <form method="post" action="{{ route('vacations.ski-areas.vote', [$vacation, $skiArea]) }}">
                            @csrf
                            <input type="hidden" name="value" value="-1">
                            <button
                                type="submit"
                                {{ $canContribute ? '' : 'disabled' }}
                                class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-sky-500 {{ $myVote === -1 ? 'bg-rose-100 text-rose-800' : 'bg-white text-slate-600 hover:bg-slate-100' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                aria-label="{{ $myVote === -1 ? 'Je stem intrekken tegen' : 'Stem tegen' }} {{ $skiArea->name }}"
                                title="{{ $myVote === -1 ? 'Je stem intrekken' : 'Vind ik niet leuk' }}"
                            >
                                <span aria-hidden="true">👎</span> {{ $skiAreaDislikers->count() }}
                            </button>
                        </form>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 flex-wrap">
                            <div>
                                <p class="text-base font-bold text-slate-900">⛷️ {{ $skiArea->name }}</p>
                                <p class="text-xs text-slate-600">Toegevoegd door {{ $skiArea->addedBy->name }}</p>
                            </div>

                            @if ($canEditSkiArea)
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="editing = true" class="rounded text-xs font-semibold text-sky-700 transition hover:text-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500">Bewerken</button>

                                    <x-confirm-form
                                        :action="route('vacations.ski-areas.destroy', [$vacation, $skiArea])"
                                        title="Skigebied verwijderen?"
                                        :message="$skiArea->name.' verdwijnt, samen met alle hotels, reacties en stemmen die eraan hangen. Dit kun je niet terugdraaien.'"
                                        class="rounded text-xs font-semibold text-rose-600 transition hover:text-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500"
                                    >Verwijderen</x-confirm-form>
                                </div>
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-700">
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
                            <a href="{{ $skiArea->ski_area_map_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-2 rounded text-sm text-sky-700 hover:underline focus:outline-none focus:ring-2 focus:ring-sky-500">
                                🗺️ Pistekaart bekijken
                            </a>
                        @endif

                        <div class="mt-3 flex flex-wrap items-center gap-4 border-t border-slate-200 pt-3">
                            <button type="button" @click="showComments = !showComments" :aria-expanded="showComments" class="flex items-center gap-1 rounded text-xs font-semibold text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                💬 Reacties ({{ $skiArea->comments->count() }})
                                <span x-text="showComments ? '▲' : '▼'" aria-hidden="true"></span>
                            </button>

                            @if ($skiAreaLikers->isNotEmpty() || $skiAreaDislikers->isNotEmpty())
                                <button type="button" @click="showVoters = !showVoters" :aria-expanded="showVoters" class="flex items-center gap-1 rounded text-xs font-semibold text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    👀 Wie stemde wat
                                    <span x-text="showVoters ? '▲' : '▼'" aria-hidden="true"></span>
                                </button>
                            @endif
                        </div>

                        {{-- Alleen een teller zegt weinig; hier zie je wie er voor en tegen is. --}}
                        <div x-show="showVoters" x-cloak class="mt-2 space-y-1.5">
                            @if ($skiAreaLikers->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-1">
                                    <span class="mr-1 text-[11px] font-bold uppercase tracking-wide text-emerald-800">👍 Voor</span>
                                    @foreach ($skiAreaLikers as $name)
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-300">{{ $name }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if ($skiAreaDislikers->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-1">
                                    <span class="mr-1 text-[11px] font-bold uppercase tracking-wide text-rose-800">👎 Tegen</span>
                                    @foreach ($skiAreaDislikers as $name)
                                        <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-900 ring-1 ring-rose-300">{{ $name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div x-show="showComments" x-cloak class="mt-2">
                            <div class="space-y-1.5">
                                @forelse ($skiArea->comments as $comment)
                                    <div class="flex items-start justify-between gap-2 text-sm">
                                        <p class="text-slate-700">
                                            <span class="font-semibold text-slate-900">{{ $comment->user->name }}:</span>
                                            {{ $comment->body }}
                                        </p>
                                        @if ($isAdmin || $comment->user_id === auth()->id())
                                            <x-confirm-form
                                                :action="route('vacations.ski-areas.comments.destroy', [$vacation, $skiArea, $comment])"
                                                title="Reactie verwijderen?"
                                                message="De reactie wordt definitief verwijderd."
                                                class="flex-shrink-0 rounded text-xs text-slate-600 transition hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500"
                                            >Verwijderen</x-confirm-form>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-600">Nog geen reacties.</p>
                                @endforelse
                            </div>

                            @if ($canContribute)
                                <form method="post" action="{{ route('vacations.ski-areas.comments.store', [$vacation, $skiArea]) }}" class="mt-2 flex gap-2">
                                    @csrf
                                    <label for="ski_area_comment_{{ $skiArea->id }}" class="sr-only">Reactie op {{ $skiArea->name }}</label>
                                    <input
                                        id="ski_area_comment_{{ $skiArea->id }}"
                                        type="text"
                                        name="body"
                                        placeholder="Voeg een reactie toe..."
                                        maxlength="2000"
                                        required
                                        class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500"
                                    >
                                    <button type="submit" data-busy-label="Bezig&hellip;" class="rounded text-sm font-semibold text-sky-700 transition hover:text-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500">Plaatsen</button>
                                </form>
                                <x-input-error class="mt-2" :messages="$skiAreaCommentErrors->get('body')" />
                            @endif
                        </div>
                    </div>
                </div>

                @if ($canEditSkiArea)
                    <div x-show="editing" x-cloak>
                        <form
                            method="post"
                            action="{{ route('vacations.ski-areas.update', [$vacation, $skiArea]) }}"
                            class="space-y-4"
                        >
                            @csrf
                            @method('patch')

                            @include('vacations.partials.ski-area-form-fields', [
                                'prefix' => 'ski_area_edit_'.$skiArea->id,
                                'skiArea' => $skiArea,
                                'bag' => 'skiArea'.$skiArea->id,
                            ])

                            <div class="flex items-center gap-3">
                                <x-primary-button data-busy-label="Opslaan&hellip;">Opslaan</x-primary-button>
                                <button type="button" @click="editing = false" class="rounded text-sm text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">Annuleren</button>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="mt-4 space-y-3 sm:ml-10">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Hotels in dit gebied</p>

                    @if ($isBookingPhase)
                        <p class="text-sm text-indigo-800">💶 Boekfase: check of de prijzen nog kloppen voor de gekozen datum en het aantal deelnemers voordat er geboekt wordt.</p>
                    @endif

                    @forelse ($sortedHotels as $hotel)
                        @php
                            $hotelLikers = $voterNames($hotel->votes, 1);
                            $hotelDislikers = $voterNames($hotel->votes, -1);
                            $myHotelVote = $hotel->votes->firstWhere('user_id', auth()->id())?->value;
                            $canEditHotel = $isAdmin || $hotel->user_id === auth()->id();
                            $totalPerNight = $hotel->totalPricePerNight($participantCount);
                            $hotelEditErrors = $errors->getBag('hotel'.$hotel->id);
                            $hotelCommentErrors = $errors->getBag('hotelComment'.$hotel->id);
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-white p-4" x-data="{
                            editing: {{ $hotelEditErrors->any() ? 'true' : 'false' }},
                            showComments: {{ $hotelCommentErrors->any() ? 'true' : 'false' }},
                            showVoters: false,
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
                                            <p class="text-sm font-bold text-sky-900 whitespace-nowrap">€{{ number_format($totalPerNight, 2, ',', '.') }}</p>
                                            <p class="text-[10px] leading-tight text-sky-800">per nacht p.p.</p>
                                        </div>
                                    @endif
                                    <form method="post" action="{{ route('vacations.hotels.vote', [$vacation, $skiArea, $hotel]) }}">
                                        @csrf
                                        <input type="hidden" name="value" value="1">
                                        <button
                                            type="submit"
                                            {{ $canContribute ? '' : 'disabled' }}
                                            class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-sky-500 {{ $myHotelVote === 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                            aria-label="{{ $myHotelVote === 1 ? 'Je stem intrekken voor' : 'Stem voor' }} {{ $hotel->name }}"
                                            title="{{ $myHotelVote === 1 ? 'Je stem intrekken' : 'Vind ik leuk' }}"
                                        >
                                            <span aria-hidden="true">👍</span> {{ $hotelLikers->count() }}
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('vacations.hotels.vote', [$vacation, $skiArea, $hotel]) }}">
                                        @csrf
                                        <input type="hidden" name="value" value="-1">
                                        <button
                                            type="submit"
                                            {{ $canContribute ? '' : 'disabled' }}
                                            class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-sky-500 {{ $myHotelVote === -1 ? 'bg-rose-100 text-rose-800' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                            aria-label="{{ $myHotelVote === -1 ? 'Je stem intrekken tegen' : 'Stem tegen' }} {{ $hotel->name }}"
                                            title="{{ $myHotelVote === -1 ? 'Je stem intrekken' : 'Vind ik niet leuk' }}"
                                        >
                                            <span aria-hidden="true">👎</span> {{ $hotelDislikers->count() }}
                                        </button>
                                    </form>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 flex-wrap">
                                        <div>
                                            @if ($hotel->url)
                                                <a href="{{ $hotel->url }}" target="_blank" rel="noopener noreferrer" class="rounded font-semibold text-sky-700 hover:underline focus:outline-none focus:ring-2 focus:ring-sky-500">
                                                    {{ $hotel->name }} <span aria-hidden="true">↗</span><span class="sr-only">(opent in een nieuw tabblad)</span>
                                                </a>
                                            @else
                                                <p class="font-semibold text-slate-900">{{ $hotel->name }}</p>
                                            @endif
                                            <p class="text-xs text-slate-600">Toegevoegd door {{ $hotel->addedBy->name }}</p>
                                        </div>

                                        @if ($canEditHotel)
                                            <div class="flex items-center gap-3">
                                                <button type="button" @click="editing = true" class="rounded text-xs font-semibold text-sky-700 transition hover:text-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500">Bewerken</button>

                                                <x-confirm-form
                                                    :action="route('vacations.hotels.destroy', [$vacation, $skiArea, $hotel])"
                                                    title="Hotel verwijderen?"
                                                    :message="$hotel->name.' verdwijnt, samen met de reacties en stemmen die eraan hangen.'"
                                                    class="rounded text-xs font-semibold text-rose-600 transition hover:text-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500"
                                                >Verwijderen</x-confirm-form>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($hotel->price_accommodation_per_night !== null)
                                        <p class="mt-2 text-sm text-slate-700">
                                            🏨 €{{ number_format($hotel->price_accommodation_per_night, 2, ',', '.') }} / nacht
                                            {{ $hotel->price_accommodation_unit === PriceUnit::PerPerson ? 'p.p.' : '' }}
                                        </p>
                                    @endif

                                    @if ($hotel->room_layout)
                                        <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">🛏️ {{ $hotel->room_layout }}</p>
                                    @endif

                                    <div class="mt-3 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-3">
                                        <button type="button" @click="showComments = !showComments" :aria-expanded="showComments" class="flex items-center gap-1 rounded text-xs font-semibold text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                            💬 Reacties ({{ $hotel->comments->count() }})
                                            <span x-text="showComments ? '▲' : '▼'" aria-hidden="true"></span>
                                        </button>

                                        @if ($hotelLikers->isNotEmpty() || $hotelDislikers->isNotEmpty())
                                            <button type="button" @click="showVoters = !showVoters" :aria-expanded="showVoters" class="flex items-center gap-1 rounded text-xs font-semibold text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                                👀 Wie stemde wat
                                                <span x-text="showVoters ? '▲' : '▼'" aria-hidden="true"></span>
                                            </button>
                                        @endif
                                    </div>

                                    <div x-show="showVoters" x-cloak class="mt-2 space-y-1.5">
                                        @if ($hotelLikers->isNotEmpty())
                                            <div class="flex flex-wrap items-center gap-1">
                                                <span class="mr-1 text-[11px] font-bold uppercase tracking-wide text-emerald-800">👍 Voor</span>
                                                @foreach ($hotelLikers as $name)
                                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-300">{{ $name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if ($hotelDislikers->isNotEmpty())
                                            <div class="flex flex-wrap items-center gap-1">
                                                <span class="mr-1 text-[11px] font-bold uppercase tracking-wide text-rose-800">👎 Tegen</span>
                                                @foreach ($hotelDislikers as $name)
                                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-900 ring-1 ring-rose-300">{{ $name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div x-show="showComments" x-cloak class="mt-2">
                                        <div class="space-y-1.5">
                                            @forelse ($hotel->comments as $comment)
                                                <div class="flex items-start justify-between gap-2 text-sm">
                                                    <p class="text-slate-700">
                                                        <span class="font-semibold text-slate-900">{{ $comment->user->name }}:</span>
                                                        {{ $comment->body }}
                                                    </p>
                                                    @if ($isAdmin || $comment->user_id === auth()->id())
                                                        <x-confirm-form
                                                            :action="route('vacations.hotels.comments.destroy', [$vacation, $skiArea, $hotel, $comment])"
                                                            title="Reactie verwijderen?"
                                                            message="De reactie wordt definitief verwijderd."
                                                            class="flex-shrink-0 rounded text-xs text-slate-600 transition hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500"
                                                        >Verwijderen</x-confirm-form>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-xs text-slate-600">Nog geen reacties.</p>
                                            @endforelse
                                        </div>

                                        @if ($canContribute)
                                            <form method="post" action="{{ route('vacations.hotels.comments.store', [$vacation, $skiArea, $hotel]) }}" class="mt-2 flex gap-2">
                                                @csrf
                                                <label for="hotel_comment_{{ $hotel->id }}" class="sr-only">Reactie op {{ $hotel->name }}</label>
                                                <input
                                                    id="hotel_comment_{{ $hotel->id }}"
                                                    type="text"
                                                    name="body"
                                                    placeholder="Voeg een reactie toe..."
                                                    maxlength="2000"
                                                    required
                                                    class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500"
                                                >
                                                <button type="submit" data-busy-label="Bezig&hellip;" class="rounded text-sm font-semibold text-sky-700 transition hover:text-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500">Plaatsen</button>
                                            </form>
                                            <x-input-error class="mt-2" :messages="$hotelCommentErrors->get('body')" />
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if ($canEditHotel)
                                <div x-show="editing" x-cloak>
                                    <form
                                        method="post"
                                        action="{{ route('vacations.hotels.update', [$vacation, $skiArea, $hotel]) }}"
                                        class="space-y-4"
                                    >
                                        @csrf
                                        @method('patch')

                                        @include('vacations.partials.hotel-form-fields', [
                                            'prefix' => 'hotel_edit_'.$hotel->id,
                                            'hotel' => $hotel,
                                            'bag' => 'hotel'.$hotel->id,
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
                        <p class="text-sm text-slate-600">Nog geen hotels toegevoegd in dit gebied.</p>
                    @endforelse

                    @if ($canContribute)
                        <details class="pt-1" {{ $newHotelErrors->any() ? 'open' : '' }}>
                            <summary class="text-sm font-semibold text-sky-700 cursor-pointer select-none hover:text-sky-900">+ Hotel toevoegen aan {{ $skiArea->name }}</summary>

                            <form
                                method="post"
                                action="{{ route('vacations.hotels.store', [$vacation, $skiArea]) }}"
                                class="mt-3 space-y-4"
                                x-data="{ accommodationUnit: 'total' }"
                            >
                                @csrf

                                @include('vacations.partials.hotel-form-fields', [
                                    'prefix' => 'hotel_new_'.$skiArea->id,
                                    'hotel' => null,
                                    'bag' => 'hotelNew'.$skiArea->id,
                                ])

                                <x-primary-button data-busy-label="Bezig&hellip;">Hotel toevoegen</x-primary-button>
                            </form>
                        </details>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-600">Nog geen skigebieden toegevoegd.</p>
        @endforelse
    </div>

    @if ($canContribute)
        <details class="mt-6 border-t border-slate-100 pt-6" {{ $errors->getBag('skiAreaNew')->any() ? 'open' : '' }}>
            <summary class="text-sm font-semibold text-slate-900 cursor-pointer select-none">
                + Skigebied toevoegen
            </summary>

            <form
                method="post"
                action="{{ route('vacations.ski-areas.store', $vacation) }}"
                class="mt-4 space-y-4"
            >
                @csrf

                @include('vacations.partials.ski-area-form-fields', [
                    'prefix' => 'ski_area_new',
                    'skiArea' => null,
                    'bag' => 'skiAreaNew',
                ])

                <x-primary-button data-busy-label="Bezig&hellip;">Skigebied toevoegen</x-primary-button>
            </form>
        </details>
    @endif
</div>
