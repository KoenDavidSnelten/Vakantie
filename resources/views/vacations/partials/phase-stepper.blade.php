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
        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $isCurrent ? $phase->badgeClasses() : ($isDone ? 'bg-slate-100 text-slate-400' : 'bg-slate-50 text-slate-300') }}">
            @if ($isDone) <span aria-hidden="true">✓</span> @endif
            {{ $phase->label() }}
        </span>

        @unless ($loop->last)
            <span class="text-slate-300" aria-hidden="true">&rarr;</span>
        @endunless
    @endforeach

    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
        <button
            type="button"
            @click="open = !open"
            @keydown.escape.window="open = false"
            :aria-expanded="open"
            aria-label="Uitleg over de fases"
            class="ml-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-slate-500 text-xs font-bold ring-1 ring-slate-200 hover:bg-slate-200 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
        >
            i
        </button>

        <div
            x-show="open"
            x-cloak
            @click.outside="open = false"
            x-transition.opacity
            class="absolute right-0 z-20 mt-2 w-72 sm:w-80 rounded-2xl bg-white p-4 text-left shadow-lg ring-1 ring-slate-200"
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
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-sky-600">Huidige fase</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
