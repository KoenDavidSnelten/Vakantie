@props([
    'action',
    'method' => 'delete',
    'title' => 'Weet je het zeker?',
    'message' => null,
    'confirm' => 'Verwijderen',
])

{{--
    Een verwijderknop met een echte bevestigingsdialoog in plaats van een kale
    browser-confirm(). De dialoog wordt naar <body> geteleporteerd, zodat hij niet
    wordt afgeknipt door kaarten met overflow-hidden of horizontaal scrollende tabellen.
--}}
<div x-data="{ open: false }" x-id="['confirm-title']" class="contents">
    <button type="button" x-on:click="open = true" {{ $attributes }}>{{ $slot }}</button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[70] flex items-end justify-center p-4 sm:items-center"
            x-on:keydown.escape.window="open = false"
            role="dialog"
            aria-modal="true"
            x-bind:aria-labelledby="$id('confirm-title')"
        >
            <div
                x-show="open"
                x-transition.opacity.duration.200ms
                class="absolute inset-0 bg-slate-900/60"
                x-on:click="open = false"
                aria-hidden="true"
            ></div>

            <div
                x-show="open"
                x-transition
                x-effect="if (open) $nextTick(() => $refs.confirmButton?.focus())"
                class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200"
            >
                <h2 x-bind:id="$id('confirm-title')" class="text-base font-bold text-slate-900">{{ $title }}</h2>

                @if ($message)
                    <p class="mt-2 text-sm text-slate-600">{{ $message }}</p>
                @endif

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        x-on:click="open = false"
                        class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                    >
                        Annuleren
                    </button>

                    <form method="post" action="{{ $action }}">
                        @csrf
                        @method($method)

                        <button
                            type="submit"
                            x-ref="confirmButton"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                        >
                            {{ $confirm }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
