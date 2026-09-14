@php
    $phases = \App\Enums\VacationPhase::cases();
    $currentIndex = array_search($vacation->phase, $phases, true);
@endphp

<div class="flex items-center flex-wrap gap-1.5">
    @foreach ($phases as $index => $phase)
        @php
            $isCurrent = $phase === $vacation->phase;
            $isDone = $index < $currentIndex;
        @endphp
        <span
            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $isCurrent ? $phase->badgeClasses() : ($isDone ? 'bg-slate-200 text-slate-600' : 'bg-slate-100 text-slate-500') }}"
            @if ($isCurrent) aria-current="step" @endif
        >
            @if ($isDone) <span aria-hidden="true">✓</span> @endif
            {{ $phase->label() }}
            @if ($isCurrent) <span class="sr-only">(huidige fase)</span> @endif
        </span>

        @unless ($loop->last)
            <span class="text-slate-400" aria-hidden="true">&rarr;</span>
        @endunless
    @endforeach

    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
        <button
            type="button"
            @click="open = !open"
            @keydown.escape.window="open = false"
            :aria-expanded="open"
            aria-label="Uitleg over de fases"
            class="ml-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-slate-700 text-xs font-bold ring-1 ring-slate-300 transition hover:bg-slate-300 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1"
        >
            i
        </button>

        {{-- max-w houdt de popover op smalle schermen binnen beeld; hij hangt rechts
             uitgelijnd omdat de knop zelf helemaal rechts in de rij staat. --}}
        <div
            x-show="open"
            x-cloak
            @click.outside="open = false"
            x-transition.opacity
            class="absolute right-0 z-20 mt-2 w-72 max-w-[calc(100vw-2.5rem)] sm:w-80 rounded-2xl bg-white p-4 text-left shadow-lg ring-1 ring-slate-200"
        >
            <p class="text-sm font-semibold text-slate-900">De fases van een vakantie</p>
            <ul class="mt-3 space-y-3">
                @foreach ($phases as $index => $phase)
                    @php $isCurrent = $phase === $vacation->phase; @endphp
                    <li>
                        <span class="flex w-full items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $phase->badgeClasses() }}">
                            {{ $phase->label() }}
                        </span>
                        <div class="mt-1.5">
                            <p class="text-xs leading-relaxed text-slate-600">{{ $phase->description() }}</p>
                            @if ($isCurrent)
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-sky-700">Huidige fase</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
