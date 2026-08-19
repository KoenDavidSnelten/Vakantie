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
</div>
