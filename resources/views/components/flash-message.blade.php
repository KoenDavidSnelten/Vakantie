@php
    /**
     * Alle controllers flashen een korte statuscode; hier staat de bijbehorende
     * melding. Staat een code hier niet in, dan tonen we niets (bijvoorbeeld
     * 'verification-link-sent', die op de profielpagina in context wordt getoond).
     */
    $messages = [
        'vacation-created' => ['Vakantie aangemaakt. Voeg hieronder de deelnemers toe.', 'success'],
        'vacation-updated' => ['Wijzigingen opgeslagen.', 'success'],
        'date-range-updated' => ['Datumbereik bijgewerkt.', 'success'],
        'availability-updated' => ['Je beschikbaarheid is opgeslagen.', 'success'],
        'availability-cleared' => ['Beschikbaarheid gewist.', 'success'],
        'final-date-set' => ['De definitieve datum staat vast.', 'success'],
        'ski-area-added' => ['Skigebied toegevoegd.', 'success'],
        'ski-area-updated' => ['Skigebied bijgewerkt.', 'success'],
        'ski-area-removed' => ['Skigebied verwijderd.', 'info'],
        'hotel-added' => ['Hotel toegevoegd.', 'success'],
        'hotel-updated' => ['Hotel bijgewerkt.', 'success'],
        'hotel-removed' => ['Hotel verwijderd.', 'info'],
        'comment-added' => ['Reactie geplaatst.', 'success'],
        'comment-removed' => ['Reactie verwijderd.', 'info'],
        'vote-updated' => ['Je stem is opgeslagen.', 'success'],
        'vehicle-added' => ['Auto toegevoegd.', 'success'],
        'vehicle-updated' => ['Auto bijgewerkt.', 'success'],
        'vehicle-removed' => ['Auto verwijderd.', 'info'],
        'passengers-updated' => ['De auto-indeling is bijgewerkt.', 'success'],
        'travel-option-added' => ['Reisoptie toegevoegd.', 'success'],
        'travel-option-updated' => ['Reisoptie bijgewerkt.', 'success'],
        'travel-option-removed' => ['Reisoptie verwijderd.', 'info'],
        'packing-item-added' => ['Item toegevoegd aan de paklijst.', 'success'],
        'packing-item-removed' => ['Item van de paklijst gehaald.', 'info'],
        'profile-updated' => ['Je profiel is bijgewerkt.', 'success'],
        'password-updated' => ['Je wachtwoord is gewijzigd.', 'success'],
    ];

    [$message, $tone] = $messages[session('status')] ?? [null, null];

    $tones = [
        'success' => ['bg-emerald-50 ring-emerald-300 text-emerald-900', '✓', 'bg-emerald-600'],
        'info' => ['bg-slate-100 ring-slate-300 text-slate-800', 'i', 'bg-slate-600'],
    ];
@endphp

@if ($message)
    @php [$box, $icon, $iconBackground] = $tones[$tone]; @endphp

    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition.opacity.duration.200ms
        x-init="setTimeout(() => show = false, 5000)"
        role="status"
        aria-live="polite"
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4"
    >
        <div class="flex items-center gap-3 rounded-xl px-4 py-3 shadow-sm ring-1 {{ $box }}">
            <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {{ $iconBackground }}" aria-hidden="true">{{ $icon }}</span>

            <p class="flex-1 text-sm font-semibold">{{ $message }}</p>

            <button
                type="button"
                x-on:click="show = false"
                aria-label="Melding sluiten"
                class="flex-shrink-0 rounded-md p-1 text-current opacity-60 transition hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M5 5l10 10M15 5L5 15" />
                </svg>
            </button>
        </div>
    </div>
@endif
