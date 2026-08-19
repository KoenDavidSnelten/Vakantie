@php
    use App\Enums\PackingCategory;

    $isAdmin = auth()->user()->isAdmin();
    $isParticipant = $vacation->users->contains('id', auth()->id());
    $myUserId = auth()->id();
    $itemsByCategory = $vacation->packingItems->groupBy('category');
    $totalItems = $vacation->packingItems->count();
    $myCheckedIds = $vacation->packingItems->filter(fn ($item) => $item->isCheckedBy($myUserId))->pluck('id');
@endphp

<div
    class="bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-100 rounded-2xl"
    x-data="{
        checked: [{{ $myCheckedIds->implode(',') }}],
        isChecked(id) { return this.checked.includes(id) },
        toggle(id, url) {
            const wasChecked = this.isChecked(id);
            this.checked = wasChecked ? this.checked.filter(i => i !== id) : [...this.checked, id];
            fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            }).then(response => {
                if (! response.ok) throw new Error('toggle failed');
            }).catch(() => {
                this.checked = wasChecked ? [...this.checked, id] : this.checked.filter(i => i !== id);
            });
        },
    }"
>
    <div class="flex items-center justify-between flex-wrap gap-2">
        <h3 class="text-lg font-medium text-slate-900">🧳 Paklijst</h3>

        @if ($totalItems > 0)
            <span class="text-sm text-slate-500"><span x-text="checked.length"></span> / {{ $totalItems }} ingepakt</span>
        @endif
    </div>
    <p class="mt-1 text-sm text-slate-500">Vink af wat jij hebt ingepakt, iedereen houdt zijn eigen voortgang bij.</p>

    <div class="mt-6 space-y-6">
        @foreach (PackingCategory::cases() as $category)
            @php $items = $itemsByCategory->get($category->value, collect())->sortBy('name'); @endphp
            @if ($items->isNotEmpty())
                <div>
                    <h4 class="text-sm font-semibold text-slate-900">{{ $category->icon() }} {{ $category->label() }}</h4>
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
                                    class="flex min-w-0 flex-1 items-center gap-2.5 text-left disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span
                                        class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded border-2 text-xs text-white transition"
                                        :class="isChecked({{ $item->id }}) ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300'"
                                    >
                                        <span x-show="isChecked({{ $item->id }})">✓</span>
                                    </span>
                                    <span
                                        class="truncate text-sm"
                                        :class="isChecked({{ $item->id }}) ? 'text-slate-400 line-through' : 'text-slate-700'"
                                    >
                                        {{ $item->name }}
                                    </span>
                                </button>

                                @if ($canDelete)
                                    <form method="post" action="{{ route('vacations.packing-list.destroy', [$vacation, $item]) }}" onsubmit="return confirm('Item verwijderen?')">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="flex-shrink-0 text-xs text-slate-300 hover:text-rose-600">✕</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        @if ($totalItems === 0)
            <p class="text-sm text-slate-500">Nog geen items op de paklijst.</p>
        @endif
    </div>

    @if ($isAdmin || $isParticipant)
        <details class="mt-6 border-t border-slate-100 pt-6" {{ $errors->any() ? 'open' : '' }}>
            <summary class="text-sm font-semibold text-slate-900 cursor-pointer select-none">
                + Item toevoegen
            </summary>

            <form method="post" action="{{ route('vacations.packing-list.store', $vacation) }}" class="mt-4 flex flex-wrap items-end gap-4">
                @csrf

                <div>
                    <x-input-label for="packing_item_name" value="Naam" />
                    <x-text-input id="packing_item_name" name="name" class="mt-1 block w-full" :value="old('name')" placeholder="Bijv. Zonnebril" required />
                </div>

                <div>
                    <x-input-label for="packing_item_category" value="Categorie" />
                    <select id="packing_item_category" name="category" class="mt-1 block w-full border-slate-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg shadow-sm">
                        @foreach (PackingCategory::cases() as $category)
                            <option value="{{ $category->value }}" @selected(old('category') === $category->value)>
                                {{ $category->icon() }} {{ $category->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-primary-button>Toevoegen</x-primary-button>
            </form>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
            <x-input-error class="mt-2" :messages="$errors->get('category')" />
        </details>
    @endif
</div>
