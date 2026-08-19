@php
    use App\Enums\AvailabilityStatus;
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

    $calendarWeeks = collect();
    if (! empty($dates)) {
        $calendarStart = $vacation->planning_start_date->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $vacation->planning_end_date->copy()->endOfWeek(Carbon::SUNDAY);

        $calendarWeeks = collect(CarbonPeriod::create($calendarStart, $calendarEnd))
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->chunk(7);
    }
@endphp

<div class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-100 rounded-2xl">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <h3 class="text-lg font-medium text-slate-900">📅 Datumplanner</h3>

        @if ($isFinalized)
            <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700">
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

                <x-primary-button>Bereik instellen</x-primary-button>
            </form>
            <x-input-error class="mt-2" :messages="$errors->get('planning_start_date')" />
            <x-input-error class="mt-2" :messages="$errors->get('planning_end_date')" />
        @else
            <p class="mt-4 text-sm text-slate-500">De organisator heeft nog geen datumbereik gekozen om te peilen.</p>
        @endif
    @else
        @if ($isAdmin)
            <details class="mt-4">
                <summary class="text-sm text-sky-700 cursor-pointer select-none">
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

                    <x-primary-button>Bijwerken</x-primary-button>
                </form>
                <p class="mt-2 text-xs text-slate-500">Let op: dagen buiten het nieuwe bereik verliezen ingevulde beschikbaarheid.</p>
                <x-input-error class="mt-2" :messages="$errors->get('planning_start_date')" />
                <x-input-error class="mt-2" :messages="$errors->get('planning_end_date')" />
            </details>
        @endif

        @if ($isParticipant && $isFinalized)
            <div class="mt-6">
                <h4 class="text-sm font-semibold text-slate-900">Jouw beschikbaarheid</h4>
                <p class="mt-2 text-sm text-slate-500">🔒 De datum is definitief gekozen, beschikbaarheid kan niet meer aangepast worden.</p>
            </div>
        @elseif ($isParticipant)
            <div class="mt-6">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h4 class="text-sm font-semibold text-slate-900">Jouw beschikbaarheid</h4>

                    <div class="flex items-center gap-3 text-xs text-slate-500">
                        @foreach (AvailabilityStatus::cases() as $option)
                            <span class="inline-flex items-center gap-1.5">
                                <span class="block h-3 w-3 rounded-full {{ $option->dotClasses() }}"></span>
                                {{ $option->label() }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <form
                    method="post"
                    action="{{ route('vacations.date-planner.availability', $vacation) }}"
                    class="mt-3"
                    x-data="{
                        dragging: false,
                        status: null,
                        changed: false,
                        saving: false,
                        save() {
                            if (! this.changed) return;
                            this.changed = false;
                            this.saving = true;
                            fetch(this.$el.action, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json' },
                                body: new FormData(this.$el),
                            }).then(response => {
                                if (! response.ok) throw new Error('save failed');
                                // Other sections (everyone's availability, suggestions) are
                                // server-rendered, so reload to pick up the saved change.
                                window.location.reload();
                            }).catch(() => { this.saving = false; });
                        },
                    }"
                    @mouseup.window="dragging = false; save()"
                    @submit.prevent="save()"
                >
                    @csrf
                    @method('put')

                    <p class="text-xs text-slate-500">Tip: klik en sleep over meerdere dagen om ze in één keer op dezelfde status te zetten. Je keuzes worden automatisch opgeslagen.</p>

                    <div class="mt-3 grid grid-cols-7 gap-1.5 text-center text-xs font-medium text-slate-500">
                        <div>Ma</div>
                        <div>Di</div>
                        <div>Wo</div>
                        <div>Do</div>
                        <div>Vr</div>
                        <div>Za</div>
                        <div>Zo</div>
                    </div>

                    @foreach ($calendarWeeks as $week)
                        <div class="mt-1.5 grid grid-cols-7 gap-1.5 select-none">
                            @foreach ($week as $date)
                                @php
                                    $inRange = in_array($date, $dates, true);
                                    $current = $myAvailability->get($date)?->status?->value;
                                @endphp
                                <div
                                    class="rounded-xl p-1.5 sm:p-2 {{ $inRange ? 'bg-slate-50' : '' }}"
                                    @if ($inRange)
                                        @mouseenter="if (dragging) { $el.querySelector('input[value=' + status + ']').checked = true; changed = true }"
                                    @endif
                                >
                                    @if ($inRange)
                                        <p class="text-center text-xs font-semibold text-slate-700">
                                            {{ Carbon::parse($date)->format('j') }}
                                        </p>
                                        <div class="mt-1.5 flex flex-col items-center justify-center gap-1 sm:gap-1.5">
                                            @foreach (AvailabilityStatus::cases() as $option)
                                                <label
                                                    class="cursor-pointer"
                                                    title="{{ $option->label() }}"
                                                    @mousedown="dragging = true; status = '{{ $option->value }}'; $el.querySelector('input').checked = true; changed = true"
                                                >
                                                    <input
                                                        type="radio"
                                                        name="dates[{{ $date }}]"
                                                        value="{{ $option->value }}"
                                                        class="peer sr-only"
                                                        @checked($current === $option->value)
                                                        required
                                                    >
                                                    <span class="block h-5 w-5 sm:h-6 sm:w-6 rounded-full border-2 bg-white {{ $option->swatchClasses() }} transition"></span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <x-input-error class="mt-2" :messages="$errors->get('dates.*')" />

                    <div class="flex items-center gap-4 pt-4 text-sm text-slate-500">
                        <p x-show="saving">Opslaan&hellip;</p>
                    </div>
                </form>
            </div>
        @endif

        <div class="mt-6 border-t border-slate-100 pt-6">
            <h4 class="text-sm font-semibold text-slate-900">Beschikbaarheid van iedereen</h4>

            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="text-left font-medium text-slate-500 pb-2 pr-4">Datum</th>
                            <th class="text-left font-medium text-slate-500 pb-2 pr-4">%</th>
                            @foreach ($participants as $participant)
                                <th class="text-center font-medium text-slate-500 pb-2 px-3">{{ $participant->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dates as $date)
                            @php
                                $canCount = $participants->filter(
                                    fn ($participant) => $availabilityByUserAndDate->get($participant->id)?->get($date)?->status === AvailabilityStatus::Can
                                )->count();
                                $canPercentage = $participants->isNotEmpty() ? (int) round($canCount / $participants->count() * 100) : 0;
                            @endphp
                            <tr class="border-t border-slate-100">
                                <td class="py-2 pr-4 text-slate-700 whitespace-nowrap">
                                    {{ Carbon::parse($date)->locale('nl')->translatedFormat('D d M') }}
                                </td>
                                <td class="py-2 pr-4 whitespace-nowrap">
                                    <span class="text-xs font-semibold {{ $canPercentage === 100 ? 'text-emerald-600' : 'text-slate-500' }}">
                                        {{ $canPercentage }}%
                                    </span>
                                </td>
                                @foreach ($participants as $participant)
                                    @php $status = $availabilityByUserAndDate->get($participant->id)?->get($date)?->status; @endphp
                                    <td class="py-2 px-3 text-center">
                                        @if ($status)
                                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold {{ $status->badgeClasses() }}">{{ $status->label() }}</span>
                                        @else
                                            <span class="text-slate-300">&ndash;</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($isAdmin)
            @php $suggestions = $vacation->topDateRangeSuggestions(); @endphp

            <div class="mt-6 border-t border-slate-100 pt-6">
                <h4 class="text-sm font-semibold text-slate-900">Definitieve datum kiezen</h4>

                @if (! empty($suggestions))
                    <div class="mt-3">
                        <p class="text-xs font-medium text-slate-500">Top suggesties</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($suggestions as $suggestion)
                                @php
                                    $everyone = $suggestion['count'] === $suggestion['total'];
                                    $startLabel = Carbon::parse($suggestion['start'])->locale('nl')->translatedFormat('j F');
                                    $endLabel = Carbon::parse($suggestion['end'])->locale('nl')->translatedFormat('j F');
                                @endphp
                                <button
                                    type="button"
                                    onclick="document.getElementById('final_start_date').value = '{{ $suggestion['start'] }}'; document.getElementById('final_end_date').value = '{{ $suggestion['end'] }}';"
                                    class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition {{ $everyone ? 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' : 'border-slate-200 text-slate-700 hover:border-sky-300 hover:bg-sky-50' }}"
                                >
                                    <span>{{ $startLabel }} &ndash; {{ $endLabel }}</span>
                                    <span class="font-semibold">
                                        {{ $everyone ? 'iedereen kan' : $suggestion['count'] . '/' . $suggestion['total'] . ' kunnen' }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-xs text-slate-400">Klik op een suggestie om de datums hieronder in te vullen.</p>
                    </div>
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

                    <x-primary-button>Bevestigen</x-primary-button>

                    @if (session('status') === 'final-date-set')
                        <p class="text-sm text-slate-600">Definitieve datum opgeslagen.</p>
                    @endif
                </form>
                <x-input-error class="mt-2" :messages="$errors->get('final_start_date')" />
                <x-input-error class="mt-2" :messages="$errors->get('final_end_date')" />
            </div>
        @endif
    @endif
</div>
