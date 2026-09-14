@php
    use App\Enums\AvatarColor;
    use App\Enums\AvatarFrame;
    use App\Enums\AvatarSymbol;

    $symbols = collect(AvatarSymbol::cases())->map(fn ($case) => [
        'value' => $case->value,
        'label' => $case->label(),
        'glyph' => $case->glyph(),
    ]);

    $colors = collect(AvatarColor::cases())->map(fn ($case) => [
        'value' => $case->value,
        'label' => $case->label(),
        'background' => $case->backgroundClass(),
    ]);

    $frames = collect(AvatarFrame::cases())->map(fn ($case) => [
        'value' => $case->value,
        'label' => $case->label(),
        'frame' => $case->frameClass(),
    ]);
@endphp

<div
    x-data="avatarPicker({
        symbols: @js($symbols),
        colors: @js($colors),
        frames: @js($frames),
        initials: @js($user->initials()),
        symbol: @js(old('avatar_symbol', $user->avatarSymbol()->value)),
        color: @js(old('avatar_color', $user->avatarColor()->value)),
        frame: @js(old('avatar_frame', $user->avatarFrame()->value)),
    })"
    class="rounded-xl border border-slate-200 bg-slate-50/50 p-4"
>
    <input type="hidden" name="avatar_symbol" :value="symbol">
    <input type="hidden" name="avatar_color" :value="color">
    <input type="hidden" name="avatar_frame" :value="frame">

    <div class="flex items-center gap-4">
        <span
            class="inline-flex h-16 w-16 shrink-0 select-none items-center justify-center rounded-full font-semibold leading-none text-white transition"
            :class="[selectedColor.background, selectedFrame.frame, selectedSymbol.glyph ? 'text-3xl' : 'text-xl']"
            x-text="selectedSymbol.glyph || initials"
            aria-hidden="true"
        ></span>

        <div>
            <p class="text-sm font-semibold text-slate-900">{{ __('Je avatar') }}</p>
            <p class="mt-0.5 text-sm text-slate-600">
                {{ __('Kies een figuur, een kleur en een randje. Je ziet meteen wat het wordt.') }}
            </p>
        </div>
    </div>

    <div class="mt-4">
        <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Figuur') }}</span>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($symbols as $option)
                <button
                    type="button"
                    @click="symbol = @js($option['value'])"
                    :aria-pressed="symbol === @js($option['value'])"
                    :class="symbol === @js($option['value'])
                        ? 'border-sky-600 ring-2 ring-sky-500'
                        : 'border-slate-200 hover:border-slate-400'"
                    class="flex h-11 w-11 items-center justify-center rounded-lg border bg-white text-xl transition focus:outline-none focus:ring-2 focus:ring-sky-500"
                    title="{{ $option['label'] }}"
                >
                    @if ($option['glyph'] === '')
                        <span class="text-xs font-semibold text-slate-700">{{ $user->initials() ?: 'AB' }}</span>
                    @else
                        {{ $option['glyph'] }}
                    @endif
                    <span class="sr-only">{{ $option['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="mt-4">
        <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Kleur') }}</span>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($colors as $option)
                <button
                    type="button"
                    @click="color = @js($option['value'])"
                    :aria-pressed="color === @js($option['value'])"
                    :class="color === @js($option['value']) ? 'ring-2 ring-slate-900 ring-offset-2' : ''"
                    class="h-8 w-8 rounded-full transition focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 {{ $option['background'] }}"
                    title="{{ $option['label'] }}"
                >
                    <span class="sr-only">{{ $option['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="mt-4">
        <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Randje') }}</span>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($frames as $option)
                <button
                    type="button"
                    @click="frame = @js($option['value'])"
                    :aria-pressed="frame === @js($option['value'])"
                    :class="frame === @js($option['value'])
                        ? 'border-sky-600 ring-2 ring-sky-500'
                        : 'border-slate-200 hover:border-slate-400'"
                    class="flex items-center gap-2 rounded-lg border bg-white px-3 py-2 text-xs font-medium text-slate-700 transition focus:outline-none focus:ring-2 focus:ring-sky-500"
                >
                    <span
                        class="inline-block h-5 w-5 rounded-full {{ $option['frame'] }}"
                        :class="selectedColor.background"
                        aria-hidden="true"
                    ></span>
                    {{ $option['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <x-input-error class="mt-2" :messages="$errors->get('avatar_symbol')" />
    <x-input-error class="mt-2" :messages="$errors->get('avatar_color')" />
    <x-input-error class="mt-2" :messages="$errors->get('avatar_frame')" />
</div>

<script>
    window.avatarPicker = function (config) {
        return {
            ...config,

            // Terugvallen op het eerste item als er iets onbekends in de
            // database staat, zodat de preview nooit leeg blijft.
            pick(list, value) {
                return list.find((option) => option.value === value) ?? list[0];
            },

            get selectedSymbol() {
                return this.pick(this.symbols, this.symbol);
            },

            get selectedColor() {
                return this.pick(this.colors, this.color);
            },

            get selectedFrame() {
                return this.pick(this.frames, this.frame);
            },
        };
    };
</script>
