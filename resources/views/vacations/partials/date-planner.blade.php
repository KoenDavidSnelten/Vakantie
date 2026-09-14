@php
    use App\Enums\AvailabilityStatus;
    use App\Http\Controllers\VacationDatePlannerController;
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;

    $dates = $vacation->planningDates();
    $participants = $vacation->users;
    $isAdmin = auth()->user()->isAdmin();
    $isParticipant = $participants->contains('id', auth()->id());
    $isFinalized = $vacation->final_start_date && $vacation->final_end_date;

    $availabilityByUserAndDate = $vacation->dateAvailabilities
        ->groupBy('user_id')
        ->map(fn ($items) => $items->keyBy(fn ($item) => $item->date->format('Y-m-d')));

    $myAvailability = $availabilityByUserAndDate->get(auth()->id(), collect());

    // De planner wordt per maand doorgebladerd: elke maand binnen het bereik krijgt
    // een eigen kalenderrooster en eigen tabelrijen.
    $months = collect();
    if (! empty($dates)) {
        $cursor = $vacation->planning_start_date->copy()->startOfMonth();
        $lastMonth = $vacation->planning_end_date->copy()->startOfMonth();

        while ($cursor <= $lastMonth) {
            $monthKey = $cursor->format('Y-m');

            $months->push([
                'key' => $monthKey,
                'label' => $cursor->locale('nl')->translatedFormat('F Y'),
                'weeks' => collect(CarbonPeriod::create(
                        $cursor->copy()->startOfWeek(Carbon::MONDAY),
                        $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)
                    ))
                    ->map(fn ($date) => $date->format('Y-m-d'))
                    ->chunk(7),
                'dates' => collect($dates)->filter(fn ($date) => str_starts_with($date, $monthKey))->values(),
            ]);

            $cursor->addMonth();
        }
    }

    $focusMonth = $vacation->final_start_date?->format('Y-m') ?? now()->format('Y-m');
    $currentMonthIndex = (int) $months->search(fn ($month) => $month['key'] === $focusMonth);

    // Initialen voor de kolomkoppen van het overzicht: met 15 deelnemers passen
    // hele namen niet, twee letters wel.
    $initials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return mb_strtoupper(count($parts) > 1
            ? mb_substr($parts[0], 0, 1).mb_substr((string) end($parts), 0, 1)
            : mb_substr($name, 0, 2));
    };

    $today = now()->format('Y-m-d');

    // Tint van een kalenderdag op basis van je eigen keuze, zodat een ingevulde
    // maand in één oogopslag leesbaar is.
    $cellTints = [
        'can' => 'bg-emerald-50 ring-emerald-300',
        'maybe' => 'bg-orange-50 ring-orange-300',
        'cannot' => 'bg-rose-50 ring-rose-300',
    ];

    // Eén letter per status, zodat het overzichtsrooster ook zonder kleur te lezen
    // is (kleurenblindheid, zwart-wit printen).
    $statusLetters = [
        'can' => 'K',
        'maybe' => 'M',
        'cannot' => 'N',
    ];

    $clearStatus = VacationDatePlannerController::CLEAR_STATUS;
@endphp

<div
    class="bg-white p-6 sm:p-8 shadow-md ring-1 ring-slate-200 rounded-2xl"
    x-data='{
        month: {{ $currentMonthIndex }},
        labels: @json($months->pluck('label')->all()),
        resumeKey: "date-planner-resume-{{ $vacation->id }}",
        init() {
            // Na het automatisch opslaan herlaadt de pagina; dan willen we terug naar
            // dezelfde maand en dezelfde scrollpositie in plaats van bovenaan januari.
            const saved = sessionStorage.getItem(this.resumeKey);
            if (! saved) return;
            sessionStorage.removeItem(this.resumeKey);

            try {
                const state = JSON.parse(saved);
                if (Number.isInteger(state.month) && state.month < this.labels.length) {
                    this.month = state.month;
                }
                if (typeof state.scroll === "number") {
                    this.$nextTick(() => window.scrollTo(0, state.scroll));
                }
            } catch (e) { /* kapotte state negeren we gewoon */ }
        },
        rememberPosition() {
            sessionStorage.setItem(this.resumeKey, JSON.stringify({ month: this.month, scroll: window.scrollY }));
        },
    }'
>
    <div class="flex items-center justify-between flex-wrap gap-2">
        <h3 class="text-lg font-bold text-slate-900">📅 Datumplanner</h3>

        @if ($isFinalized)
            <span class="inline-block rounded-full px-3 py-1 text-xs font-bold bg-emerald-600 text-white shadow-sm">
                Definitief: {{ $vacation->final_start_date->format('d-m-Y') }} t/m {{ $vacation->final_end_date->format('d-m-Y') }}
            </span>
        @endif
    </div>

    @if (empty($dates))
        @if ($isAdmin)
            <form method="post" action="{{ route('vacations.date-planner.range', $vacation) }}" class="mt-4 flex flex-wrap items-end gap-4">
                @csrf
                @method('patch')

                <div>
                    <x-input-label for="planning_start_date" value="Van" />
                    <x-text-input id="planning_start_date" name="planning_start_date" type="date" class="mt-1" :value="old('planning_start_date')" required />
                </div>

                <div>
                    <x-input-label for="planning_end_date" value="Tot en met" />
                    <x-text-input id="planning_end_date" name="planning_end_date" type="date" class="mt-1" :value="old('planning_end_date')" required />
                </div>

                <x-primary-button data-busy-label="Bezig&hellip;">Bereik instellen</x-primary-button>
            </form>
            <x-input-error class="mt-2" :messages="$errors->get('planning_start_date')" />
            <x-input-error class="mt-2" :messages="$errors->get('planning_end_date')" />
        @else
            <p class="mt-4 text-sm text-slate-600">De organisator heeft nog geen datumbereik gekozen om te peilen.</p>
        @endif
    @else
        @if ($isAdmin)
            <details class="mt-4 rounded-xl bg-slate-100 p-3 ring-1 ring-slate-200">
                <summary class="text-sm font-semibold text-sky-800 cursor-pointer select-none hover:text-sky-900">
                    Bereik aanpassen ({{ $vacation->planning_start_date->format('d-m-Y') }} t/m {{ $vacation->planning_end_date->format('d-m-Y') }})
                </summary>

                <form method="post" action="{{ route('vacations.date-planner.range', $vacation) }}" class="mt-3 flex flex-wrap items-end gap-4">
                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="planning_start_date" value="Van" />
                        <x-text-input id="planning_start_date" name="planning_start_date" type="date" class="mt-1" :value="old('planning_start_date', $vacation->planning_start_date->format('Y-m-d'))" required />
                    </div>

                    <div>
                        <x-input-label for="planning_end_date" value="Tot en met" />
                        <x-text-input id="planning_end_date" name="planning_end_date" type="date" class="mt-1" :value="old('planning_end_date', $vacation->planning_end_date->format('Y-m-d'))" required />
                    </div>

                    <x-primary-button data-busy-label="Bezig&hellip;">Bijwerken</x-primary-button>
                </form>
                <p class="mt-2 text-xs text-slate-600">Let op: dagen buiten het nieuwe bereik verliezen ingevulde beschikbaarheid.</p>
                <x-input-error class="mt-2" :messages="$errors->get('planning_start_date')" />
                <x-input-error class="mt-2" :messages="$errors->get('planning_end_date')" />
            </details>
        @endif

        <div class="mt-6 flex items-center justify-between gap-3 rounded-xl bg-slate-900 px-2 py-2 shadow-sm">
            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white disabled:opacity-25 disabled:hover:bg-white/10"
                x-bind:disabled="month === 0"
                x-on:click="month = Math.max(0, month - 1)"
                aria-label="Vorige maand"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 19 8 12l7-7" />
                </svg>
            </button>

            <p class="text-base font-bold tracking-wide text-white capitalize" aria-live="polite" x-text="labels[month]">{{ $months[$currentMonthIndex]['label'] }}</p>

            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white disabled:opacity-25 disabled:hover:bg-white/10"
                x-bind:disabled="month === labels.length - 1"
                x-on:click="month = Math.min(labels.length - 1, month + 1)"
                aria-label="Volgende maand"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m9 5 7 7-7 7" />
                </svg>
            </button>
        </div>

        @if ($isParticipant && $isFinalized)
            <div class="mt-6 rounded-2xl bg-slate-50 p-4 sm:p-5 ring-1 ring-slate-200">
                <h4 class="text-base font-bold text-slate-900">Jouw beschikbaarheid</h4>
                <p class="mt-2 text-sm text-slate-600">🔒 De datum is definitief gekozen, beschikbaarheid kan niet meer aangepast worden.</p>
            </div>
        @elseif ($isParticipant)
            <div class="mt-6 rounded-2xl bg-slate-50 p-4 sm:p-5 ring-1 ring-slate-200">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h4 class="text-base font-bold text-slate-900">Jouw beschikbaarheid</h4>

                    <div class="flex items-center gap-3 text-xs font-semibold text-slate-700">
                        @foreach (AvailabilityStatus::cases() as $option)
                            <span class="inline-flex items-center gap-1.5">
                                <span class="flex h-4 w-4 items-center justify-center rounded-full text-[9px] font-bold text-white ring-1 ring-slate-900/20 {{ $option->dotClasses() }}" aria-hidden="true">{{ $statusLetters[$option->value] }}</span>
                                {{ $option->label() }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <form
                    method="post"
                    action="{{ route('vacations.date-planner.availability', $vacation) }}"
                    class="mt-3"
                    data-no-submit-lock
                    x-data="{
                        dragging: false,
                        status: null,
                        changed: false,
                        saving: false,
                        failed: false,
                        /** Zet één dagcel op de status die op dit moment gesleept wordt. */
                        paint(cell) {
                            if (! this.dragging || ! this.status) return;
                            const input = cell.querySelector('input[value=&quot;' + this.status + '&quot;]');
                            if (! input) return;
                            input.checked = true;
                            this.changed = true;
                        },
                        /** Slepen met een vinger: zoek de cel onder de vinger op. */
                        onTouchMove(event) {
                            if (! this.dragging) return;
                            event.preventDefault();
                            const touch = event.touches[0];
                            const cell = document.elementFromPoint(touch.clientX, touch.clientY)?.closest('[data-date]');
                            if (cell) this.paint(cell);
                        },
                        start(status, input) {
                            this.dragging = true;
                            this.status = status;
                            input.checked = true;
                            this.changed = true;
                        },
                        save() {
                            if (! this.changed) return;
                            this.changed = false;
                            this.saving = true;
                            this.failed = false;
                            fetch(this.$el.action, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json' },
                                body: new FormData(this.$el),
                            }).then(response => {
                                if (! response.ok) throw new Error('save failed');
                                // De andere blokken (ieders beschikbaarheid, de suggesties)
                                // komen van de server, dus herladen we. De maand en de
                                // scrollpositie onthouden we, anders spring je naar boven.
                                this.rememberPosition();
                                window.location.reload();
                            }).catch(() => { this.saving = false; this.failed = true; this.changed = true; });
                        },
                    }"
                    @mouseup.window="dragging = false; save()"
                    @touchend.window="dragging = false; save()"
                    @touchcancel.window="dragging = false"
                    @submit.prevent="save()"
                >
                    @csrf
                    @method('put')

                    <p class="text-xs text-slate-600">
                        Tik op een bolletje, of sleep met je vinger of muis over meerdere dagen om ze in één keer op dezelfde status te zetten.
                        Met de <span class="font-semibold">✕</span> maak je een dag weer leeg. Je keuzes worden automatisch opgeslagen.
                    </p>

                    <div class="mt-3 rounded-xl bg-white p-2 sm:p-3 ring-1 ring-slate-200" @touchmove="onTouchMove($event)">
                        <div class="grid grid-cols-7 gap-1.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-600">
                            <div>Ma</div>
                            <div>Di</div>
                            <div>Wo</div>
                            <div>Do</div>
                            <div>Vr</div>
                            <div class="text-slate-900">Za</div>
                            <div class="text-slate-900">Zo</div>
                        </div>

                        @foreach ($months as $index => $calendarMonth)
                            <div x-show="month === {{ $index }}" @if ($index !== $currentMonthIndex) style="display: none" @endif>
                                @foreach ($calendarMonth['weeks'] as $week)
                                    <div class="mt-1.5 grid grid-cols-7 gap-1 sm:gap-1.5 select-none">
                                        @foreach ($week as $date)
                                            @php
                                                $inRange = str_starts_with($date, $calendarMonth['key']) && in_array($date, $dates, true);
                                                $current = $myAvailability->get($date)?->status?->value;
                                                $readableDate = Carbon::parse($date)->locale('nl')->translatedFormat('l j F');
                                            @endphp
                                            <div
                                                @if ($inRange) data-date="{{ $date }}" @endif
                                                class="group/day relative rounded-xl p-1 sm:p-2 {{ $inRange ? 'ring-1 ' . ($cellTints[$current] ?? 'bg-slate-50 ring-slate-300') : '' }}"
                                                @if ($inRange)
                                                    @mouseenter="paint($el)"
                                                @endif
                                            >
                                                @if ($inRange)
                                                    <p class="text-center text-sm font-bold {{ $date === $today ? 'text-white' : 'text-slate-900' }}">
                                                        <span class="{{ $date === $today ? 'inline-block min-w-[1.5rem] rounded-full bg-slate-900 px-1' : '' }}">
                                                            {{ Carbon::parse($date)->format('j') }}
                                                        </span>
                                                    </p>

                                                    {{-- Wissen: verschijnt zodra deze dag een status heeft, zodat een
                                                         verkeerde tik terug te draaien is zonder de cel hoger te maken. --}}
                                                    <label
                                                        class="absolute right-0.5 top-0.5 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full text-[10px] font-bold text-slate-500 opacity-0 transition group-has-[input:checked]/day:opacity-100 group-has-[input:checked]/day:hover:bg-white group-has-[input:checked]/day:hover:text-slate-900 has-[:focus-visible]:opacity-100 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-sky-500"
                                                        title="Leegmaken"
                                                        @mousedown="start('{{ $clearStatus }}', $el.querySelector('input'))"
                                                        @touchstart="start('{{ $clearStatus }}', $el.querySelector('input'))"
                                                    >
                                                        <input
                                                            type="radio"
                                                            name="dates[{{ $date }}]"
                                                            value="{{ $clearStatus }}"
                                                            class="sr-only"
                                                            aria-label="{{ $readableDate }}: leegmaken"
                                                        >
                                                        <span aria-hidden="true">✕</span>
                                                    </label>

                                                    <div class="mt-1 flex flex-col items-center justify-center" role="radiogroup" aria-label="Beschikbaarheid op {{ $readableDate }}">
                                                        @foreach (AvailabilityStatus::cases() as $option)
                                                            {{-- Het label vult de hele celbreedte: het bolletje blijft klein,
                                                                 maar het aanraakgebied is een stuk groter. --}}
                                                            <label
                                                                class="flex w-full cursor-pointer items-center justify-center py-1 sm:py-1.5"
                                                                title="{{ $option->label() }}"
                                                                @mousedown="start('{{ $option->value }}', $el.querySelector('input'))"
                                                                @touchstart="start('{{ $option->value }}', $el.querySelector('input'))"
                                                            >
                                                                <input
                                                                    type="radio"
                                                                    name="dates[{{ $date }}]"
                                                                    value="{{ $option->value }}"
                                                                    class="peer sr-only"
                                                                    aria-label="{{ $readableDate }}: {{ $option->label() }}"
                                                                    @checked($current === $option->value)
                                                                >
                                                                <span class="block h-6 w-6 rounded-full border-2 bg-white shadow-sm transition hover:scale-110 peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-slate-900/15 peer-focus-visible:ring-2 peer-focus-visible:ring-sky-500 peer-focus-visible:ring-offset-1 {{ $option->swatchClasses() }}"></span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <x-input-error class="mt-2" :messages="$errors->get('dates.*')" />

                    <div class="flex items-center gap-4 pt-4 text-sm font-semibold" aria-live="polite">
                        <p x-show="saving" x-cloak class="text-slate-700">Opslaan&hellip;</p>
                        <p x-show="failed" x-cloak class="text-rose-700">Opslaan is niet gelukt. Controleer je verbinding en tik nog een keer op een dag.</p>
                    </div>
                </form>
            </div>
        @endif

        <div class="mt-6 rounded-2xl bg-slate-50 p-4 sm:p-5 ring-1 ring-slate-200">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h4 class="text-base font-bold text-slate-900">Beschikbaarheid van iedereen</h4>

                <div class="flex items-center gap-3 text-xs font-semibold text-slate-700">
                    @foreach (AvailabilityStatus::cases() as $option)
                        <span class="inline-flex items-center gap-1.5">
                            <span class="flex h-4 w-4 items-center justify-center rounded text-[9px] font-bold text-white ring-1 ring-slate-900/20 {{ $option->solidClasses() }}" aria-hidden="true">{{ $statusLetters[$option->value] }}</span>
                            {{ $option->label() }}
                        </span>
                    @endforeach
                </div>
            </div>

            @if ($participants->count() > 6)
                <p class="mt-2 text-xs text-slate-600">Scroll opzij voor de overige deelnemers &ndash; datum en % blijven staan.</p>
            @endif

            <div class="mt-3 overflow-x-auto rounded-xl bg-white ring-1 ring-slate-200">
                {{-- border-separate: sticky kolommen werken niet met een collapsed table-border. --}}
                <table class="w-full border-separate border-spacing-0 text-sm">
                    <caption class="sr-only">Wie kan wanneer, per dag van het gepeilde bereik</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="sticky left-0 z-30 w-[7.5rem] min-w-[7.5rem] bg-slate-100 py-2.5 pl-3 pr-2 text-left text-xs font-bold uppercase tracking-wide text-slate-700">Datum</th>
                            <th scope="col" class="sticky left-[7.5rem] z-30 w-[4rem] min-w-[4rem] border-r border-slate-300 bg-slate-100 py-2.5 pr-3 text-left text-xs font-bold uppercase tracking-wide text-slate-700" title="Percentage van de deelnemers dat op deze dag kan">
                                % kan
                            </th>
                            @foreach ($participants as $participant)
                                <th scope="col" class="bg-slate-100 px-1.5 py-2">
                                    <span
                                        class="mx-auto flex h-7 w-7 items-center justify-center rounded-full text-[11px] font-bold {{ $participant->id === auth()->id() ? 'bg-sky-700 text-white ring-2 ring-sky-300' : 'bg-slate-800 text-white' }}"
                                        title="{{ $participant->name }}"
                                    >
                                        <span aria-hidden="true">{{ $initials($participant->name) }}</span>
                                        <span class="sr-only">{{ $participant->name }}</span>
                                    </span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    @foreach ($months as $index => $tableMonth)
                        <tbody x-show="month === {{ $index }}" @if ($index !== $currentMonthIndex) style="display: none" @endif>
                            @foreach ($tableMonth['dates'] as $date)
                                @php
                                    $canCount = $participants->filter(
                                        fn ($participant) => $availabilityByUserAndDate->get($participant->id)?->get($date)?->status === AvailabilityStatus::Can
                                    )->count();
                                    $canPercentage = $participants->isNotEmpty() ? (int) round($canCount / $participants->count() * 100) : 0;
                                    // Ondoorzichtige achtergrond: de sticky kolommen schuiven over de rest heen.
                                    $rowBackground = Carbon::parse($date)->isWeekend() ? 'bg-slate-100' : 'bg-white';
                                @endphp
                                <tr>
                                    <th scope="row" class="sticky left-0 z-20 w-[7.5rem] min-w-[7.5rem] border-t border-slate-200 py-2 pl-3 pr-2 text-left font-semibold text-slate-900 whitespace-nowrap {{ $rowBackground }}">
                                        {{-- Vandaag krijgt een pil om het label heen: een los badge zou de kolom
                                             breder maken dan de sticky-offset van de %-kolom. --}}
                                        <span class="{{ $date === $today ? 'rounded-full bg-slate-900 px-1.5 py-0.5 text-white' : '' }}">
                                            {{ Carbon::parse($date)->locale('nl')->translatedFormat('D d M') }}
                                        </span>
                                    </th>
                                    <td class="sticky left-[7.5rem] z-20 border-r border-t border-r-slate-300 border-t-slate-200 py-2 pr-3 whitespace-nowrap {{ $rowBackground }}">
                                        <span
                                            class="inline-block rounded-full px-2 py-0.5 text-xs font-bold {{ $canPercentage === 100 ? 'bg-emerald-700 text-white' : ($canPercentage >= 50 ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-800') }}"
                                            title="{{ $canCount }} van de {{ $participants->count() }} kan op deze dag"
                                        >
                                            {{ $canPercentage }}%
                                        </span>
                                    </td>
                                    @foreach ($participants as $participant)
                                        @php $status = $availabilityByUserAndDate->get($participant->id)?->get($date)?->status; @endphp
                                        <td class="border-t border-slate-200 px-1.5 py-2 {{ $rowBackground }}">
                                            {{-- Letter én kleur: alleen kleur is niet te lezen als je kleurenblind bent. --}}
                                            <span
                                                class="mx-auto flex h-5 w-5 items-center justify-center rounded-md text-[10px] font-bold {{ $status ? $status->solidClasses().' text-white' : 'bg-slate-100 text-slate-400 ring-1 ring-inset ring-slate-300' }}"
                                                title="{{ $participant->name }}: {{ $status?->label() ?? 'nog niet ingevuld' }}"
                                            >
                                                <span aria-hidden="true">{{ $status ? $statusLetters[$status->value] : '·' }}</span>
                                                <span class="sr-only">{{ $participant->name }}: {{ $status?->label() ?? 'nog niet ingevuld' }}</span>
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                </table>
            </div>

            {{-- Wie is welke kolom? De tooltip op de initialen werkt niet op een telefoon. --}}
            @if ($participants->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach ($participants as $participant)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">
                            <span class="flex h-4 w-4 items-center justify-center rounded-full text-[9px] font-bold text-white {{ $participant->id === auth()->id() ? 'bg-sky-700' : 'bg-slate-800' }}" aria-hidden="true">{{ $initials($participant->name) }}</span>
                            {{ $participant->name }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($isAdmin)
            @php
                // Beide telwijzen gaan mee naar de client, zodat het schuiven met de
                // minimumduur en het omzetten van de teller direct zoekt zonder herladen.
                $withLabels = fn (array $candidates) => collect($candidates)
                    ->map(fn ($candidate) => $candidate + [
                        'label' => Carbon::parse($candidate['start'])->locale('nl')->translatedFormat('D j M')
                            .' t/m '.Carbon::parse($candidate['end'])->locale('nl')->translatedFormat('D j M'),
                    ])
                    ->values();

                $candidateSets = [
                    'strict' => $withLabels($vacation->dateRangeCandidates()),
                    'relaxed' => $withLabels($vacation->dateRangeCandidates(true)),
                ];

                $people = $participants->mapWithKeys(fn ($participant) => [
                    $participant->id => ['name' => $participant->name, 'initials' => $initials($participant->name)],
                ]);

                $hasCandidates = $candidateSets['strict']->isNotEmpty() || $candidateSets['relaxed']->isNotEmpty();
                $defaultMinimumDays = \App\Models\Vacation::MINIMUM_TRIP_DAYS;
            @endphp

            <div
                class="mt-6 rounded-2xl bg-slate-50 p-4 sm:p-5 ring-1 ring-slate-200"
                x-data='{
                    mode: "strict",
                    minDays: {{ $defaultMinimumDays }},
                    people: @json($people),
                    sets: @json($candidateSets),
                    get candidates() {
                        return this.sets[this.mode];
                    },
                    get longest() {
                        return this.candidates.reduce((longest, option) => Math.max(longest, option.length), 1);
                    },
                    get days() {
                        return Math.min(this.longest, Math.max(1, this.minDays || 1));
                    },
                    get matches() {
                        const found = this.candidates.filter(option => option.length >= this.days);

                        return found.length
                            ? found.slice(0, 5)
                            : [...this.candidates].sort((a, b) => b.length - a.length).slice(0, 5);
                    },
                    get fallback() {
                        return ! this.candidates.some(option => option.length >= this.days);
                    },
                    step(delta) {
                        this.minDays = Math.min(this.longest, Math.max(1, this.minDays + delta));
                    },
                    fill(option) {
                        const start = document.getElementById("final_start_date");
                        start.value = option.start;
                        document.getElementById("final_end_date").value = option.end;
                        // Het formulier staat verderop; breng de beheerder er meteen heen.
                        start.scrollIntoView({ behavior: "smooth", block: "center" });
                        start.focus({ preventScroll: true });
                    },
                }'
            >
                <h4 class="text-base font-bold text-slate-900">Definitieve datum kiezen</h4>

                @if ($hasCandidates)
                    <div class="mt-3 rounded-xl bg-white p-3 ring-1 ring-slate-200">
                        <div class="flex flex-wrap items-end gap-x-6 gap-y-3">
                            <div>
                                <label for="min_days" class="block text-xs font-bold uppercase tracking-wide text-slate-700">Minimaal aantal dagen</label>
                                <div class="mt-1 inline-flex items-center gap-1">
                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-200 text-lg font-bold text-slate-800 transition hover:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:opacity-40"
                                        x-bind:disabled="minDays &lt;= 1"
                                        x-on:click="step(-1)"
                                        aria-label="Een dag korter"
                                    >&minus;</button>

                                    <input
                                        id="min_days"
                                        type="number"
                                        min="1"
                                        x-bind:max="longest"
                                        x-model.number="minDays"
                                        x-on:change="minDays = days"
                                        class="h-9 w-16 rounded-lg border-slate-300 text-center text-sm font-bold text-slate-900 focus:border-sky-500 focus:ring-sky-500"
                                    >

                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-200 text-lg font-bold text-slate-800 transition hover:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:opacity-40"
                                        x-bind:disabled="minDays >= longest"
                                        x-on:click="step(1)"
                                        aria-label="Een dag langer"
                                    >+</button>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-700">Wie tellen mee</p>
                                <div class="mt-1 inline-flex rounded-lg bg-slate-200 p-0.5">
                                    <button
                                        type="button"
                                        class="rounded-md px-3 py-1.5 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-sky-500"
                                        x-on:click="mode = 'strict'"
                                        x-bind:class="mode === 'strict' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-700 hover:text-slate-900'"
                                    >
                                        Alleen &#128994; kan
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md px-3 py-1.5 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-sky-500"
                                        x-on:click="mode = 'relaxed'"
                                        x-bind:class="mode === 'relaxed' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-700 hover:text-slate-900'"
                                    >
                                        &#128994; kan + &#128992; misschien
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p class="mt-2 text-xs text-slate-600">
                            Standaard zoeken we reeksen van minstens {{ \App\Models\Vacation::MINIMUM_TRIP_DAYS }} dagen: liever een paar mensen die niet kunnen dan een halve vakantie.
                        </p>

                        <div class="mt-3">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-700">
                                Beste opties van <span x-text="days"></span> dagen of langer
                            </p>

                            <p class="mt-1 text-xs font-semibold text-amber-800" x-show="fallback" style="display: none">
                                Geen enkele reeks haalt <span x-text="days"></span> dagen &ndash; hieronder de langste reeksen die er wel zijn (max. <span x-text="longest"></span> dagen).
                            </p>

                            <div class="mt-2 space-y-2">
                                <template x-for="option in matches" x-bind:key="mode + option.start + option.end">
                                    <div
                                        class="rounded-xl border p-3"
                                        x-bind:class="option.count === option.total ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-slate-50'"
                                    >
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <p class="text-sm font-bold text-slate-900" x-text="option.label"></p>
                                                <p class="text-xs font-semibold text-slate-700">
                                                    <span x-text="option.length + ' dagen'"></span>
                                                    &middot;
                                                    <span x-text="option.count === option.total ? 'iedereen kan' : option.count + ' van de ' + option.total + ' kunnen'"></span>
                                                </p>
                                            </div>

                                            <button
                                                type="button"
                                                x-on:click="fill(option)"
                                                class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                                            >
                                                Kies deze datum
                                            </button>
                                        </div>

                                        <div class="mt-2 flex flex-wrap items-center gap-1" x-show="option.can.length">
                                            <span class="mr-1 text-[11px] font-bold uppercase tracking-wide text-emerald-800">Kan</span>
                                            <template x-for="id in option.can" x-bind:key="'can' + id">
                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-300" x-text="people[id].name"></span>
                                            </template>
                                        </div>

                                        <div class="mt-1.5 flex flex-wrap items-center gap-1" x-show="option.maybe.length">
                                            <span class="mr-1 text-[11px] font-bold uppercase tracking-wide text-orange-800">Misschien</span>
                                            <template x-for="id in option.maybe" x-bind:key="'maybe' + id">
                                                <span class="rounded-full bg-orange-100 px-2 py-0.5 text-[11px] font-semibold text-orange-900 ring-1 ring-orange-300" x-text="people[id].name"></span>
                                            </template>
                                        </div>

                                        <div class="mt-1.5 flex flex-wrap items-center gap-1" x-show="option.cannot.length">
                                            <span class="mr-1 text-[11px] font-bold uppercase tracking-wide text-rose-800">Kan niet</span>
                                            <template x-for="id in option.cannot" x-bind:key="'cannot' + id">
                                                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-900 ring-1 ring-rose-300" x-text="people[id].name"></span>
                                            </template>
                                        </div>

                                        <div class="mt-1.5 flex flex-wrap items-center gap-1" x-show="option.unknown.length">
                                            <span class="mr-1 text-[11px] font-bold uppercase tracking-wide text-slate-700">Niet ingevuld</span>
                                            <template x-for="id in option.unknown" x-bind:key="'unknown' + id">
                                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-300" x-text="people[id].name"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <p class="mt-2 text-xs text-slate-600">Klik op &ldquo;Kies deze datum&rdquo; om de datums hieronder in te vullen.</p>
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-600">Er is nog geen enkele dag waarop iemand kan &ndash; vul eerst beschikbaarheid in.</p>
                @endif

                <form method="post" action="{{ route('vacations.date-planner.final', $vacation) }}" class="mt-3 flex flex-wrap items-end gap-4">
                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="final_start_date" value="Van" />
                        <x-text-input
                            id="final_start_date"
                            name="final_start_date"
                            type="date"
                            class="mt-1"
                            min="{{ $vacation->planning_start_date->format('Y-m-d') }}"
                            max="{{ $vacation->planning_end_date->format('Y-m-d') }}"
                            :value="old('final_start_date', $vacation->final_start_date?->format('Y-m-d'))"
                            required
                        />
                    </div>

                    <div>
                        <x-input-label for="final_end_date" value="Tot en met" />
                        <x-text-input
                            id="final_end_date"
                            name="final_end_date"
                            type="date"
                            class="mt-1"
                            min="{{ $vacation->planning_start_date->format('Y-m-d') }}"
                            max="{{ $vacation->planning_end_date->format('Y-m-d') }}"
                            :value="old('final_end_date', $vacation->final_end_date?->format('Y-m-d'))"
                            required
                        />
                    </div>

                    <x-primary-button data-busy-label="Bezig&hellip;">Bevestigen</x-primary-button>
                </form>

                <p class="mt-2 text-xs text-slate-600">
                    Zodra je bevestigt gaat de vakantie naar de boekfase en kan niemand zijn beschikbaarheid nog aanpassen.
                </p>

                <x-input-error class="mt-2" :messages="$errors->get('final_start_date')" />
                <x-input-error class="mt-2" :messages="$errors->get('final_end_date')" />
            </div>
        @endif
    @endif
</div>
