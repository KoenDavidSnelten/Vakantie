@php
    use App\Enums\PackingCategory;

    $isAdmin = auth()->user()->isAdmin();
    $isParticipant = $vacation->users->contains('id', auth()->id());
    $myUserId = auth()->id();
    $itemsByCategory = $vacation->packingItems->groupBy('category');
    $totalItems = $vacation->packingItems->count();
    $myCheckedIds = $vacation->packingItems->filter(fn ($item) => $item->isCheckedBy($myUserId))->pluck('id');
    $newItemErrors = $errors->getBag('packingItem');
@endphp

<div
    class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200 rounded-2xl"
    x-data="{
        checked: [{{ $myCheckedIds->implode(',') }}],
        failed: false,
        isChecked(id) { return this.checked.includes(id) },
        toggle(id, url) {
            const wasChecked = this.isChecked(id);
            this.checked = wasChecked ? this.checked.filter(i => i !== id) : [...this.checked, id];
            this.failed = false;
            fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            }).then(response => {
                if (! response.ok) throw new Error('toggle failed');
            }).catch(() => {
                // Terugdraaien: het vinkje is niet aangekomen bij de server.
                this.checked = wasChecked ? [...this.checked, id] : this.checked.filter(i => i !== id);
                this.failed = true;
            });
        },
    }"
>
    <div class="flex items-center justify-between flex-wrap gap-2">
        <h3 class="text-lg font-bold text-slate-900">🧳 Paklijst</h3>

        @if ($totalItems > 0)
            <span class="text-sm font-semibold text-slate-700"><span x-text="checked.length"></span> / {{ $totalItems }} ingepakt</span>
        @endif
    </div>
    <p class="mt-1 text-sm text-slate-600">Vink af wat jij hebt ingepakt, iedereen houdt zijn eigen voortgang bij.</p>

    @if ($totalItems > 0)
        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-200">
            <div class="h-full rounded-full bg-emerald-500 transition-all" x-bind:style="'width: ' + Math.round(checked.length / {{ $totalItems }} * 100) + '%'"></div>
        </div>
    @endif

    <p x-show="failed" x-cloak class="mt-3 rounded-lg bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-800 ring-1 ring-rose-200" aria-live="polite">
        Het vinkje kon niet worden opgeslagen. Controleer je verbinding en probeer het opnieuw.
    </p>

    <div class="mt-6 space-y-6">
        @foreach (PackingCategory::cases() as $category)
            @php $items = $itemsByCategory->get($category->value, collect())->sortBy('name'); @endphp
            @if ($items->isNotEmpty())
                <div>
                    <h4 class="text-sm font-bold text-slate-900">{{ $category->icon() }} {{ $category->label() }}</h4>
                    <div class="mt-2 space-y-1">
                        @foreach ($items as $item)
                            @php
                                $canDelete = $isAdmin || $item->user_id === $myUserId;
                                $toggleUrl = route('vacations.packing-list.toggle', [$vacation, $item]);
                            @endphp
                            <div class="flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 hover:bg-slate-50">
                                <button
                                    type="button"
                                    @click="toggle({{ $item->id }}, '{{ $toggleUrl }}')"
                                    {{ $isParticipant ? '' : 'disabled' }}
                                    x-bind:aria-pressed="isChecked({{ $item->id }}) ? 'true' : 'false'"
                                    class="flex min-w-0 flex-1 items-center gap-2.5 rounded-lg py-1 text-left transition focus:outline-none focus:ring-2 focus:ring-sky-500 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span
                                        class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded border-2 text-xs text-white transition"
                                        :class="isChecked({{ $item->id }}) ? 'border-emerald-600 bg-emerald-600' : 'border-slate-400'"
                                        aria-hidden="true"
                                    >
                                        <span x-show="isChecked({{ $item->id }})">✓</span>
                                    </span>
                                    <span
                                        class="truncate text-sm"
                                        :class="isChecked({{ $item->id }}) ? 'text-slate-500 line-through' : 'text-slate-800'"
                                    >
                                        {{ $item->name }}
                                    </span>
                                </button>

                                @if ($canDelete)
                                    <x-confirm-form
                                        :action="route('vacations.packing-list.destroy', [$vacation, $item])"
                                        title="Item van de paklijst halen?"
                                        :message="$item->name.' verdwijnt voor iedereen van de lijst, ook voor wie het al had afgevinkt.'"
                                        :confirm="'Verwijderen'"
                                        class="flex-shrink-0 rounded px-1 text-xs text-slate-500 transition hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500"
                                        aria-label="{{ $item->name }} van de paklijst halen"
                                    >✕</x-confirm-form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        @if ($totalItems === 0)
            <p class="text-sm text-slate-600">Nog geen items op de paklijst.</p>
        @endif
    </div>

    @if ($isAdmin || $isParticipant)
        <details class="mt-6 border-t border-slate-100 pt-6" {{ $newItemErrors->any() ? 'open' : '' }}>
            <summary class="text-sm font-semibold text-slate-900 cursor-pointer select-none">
                + Item toevoegen
            </summary>

            <form method="post" action="{{ route('vacations.packing-list.store', $vacation) }}" class="mt-4 flex flex-wrap items-end gap-4">
                @csrf

                <div>
                    <x-input-label for="packing_item_name" value="Naam" />
                    <x-text-input id="packing_item_name" name="name" class="mt-1 block w-full" :value="$newItemErrors->any() ? old('name') : ''" placeholder="Bijv. Zonnebril" required />
                </div>

                <div>
                    <x-input-label for="packing_item_category" value="Categorie" />
                    <select id="packing_item_category" name="category" class="mt-1 block w-full border-slate-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg shadow-sm">
                        @foreach (PackingCategory::cases() as $category)
                            <option value="{{ $category->value }}" @selected($newItemErrors->any() && old('category') === $category->value)>
                                {{ $category->icon() }} {{ $category->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-primary-button data-busy-label="Bezig&hellip;">Toevoegen</x-primary-button>
            </form>
            <x-input-error class="mt-2" :messages="$newItemErrors->get('name')" />
            <x-input-error class="mt-2" :messages="$newItemErrors->get('category')" />
        </details>
    @endif
</div>
