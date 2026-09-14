@props(['user', 'size' => 'md'])

@php
    // Voluit geschreven klassenamen: Tailwind scant Blade-bestanden als platte
    // tekst, dus samengestelde namen ('bg-'.$kleur.'-600') zouden uit de
    // productie-build gefilterd worden en elke avatar zou doorzichtig zijn.
    $palette = [
        'bg-sky-600',
        'bg-emerald-600',
        'bg-amber-600',
        'bg-rose-600',
        'bg-violet-600',
        'bg-teal-600',
        'bg-indigo-600',
        'bg-orange-600',
    ];

    $sizes = [
        'sm' => 'h-6 w-6 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-16 w-16 text-xl',
    ];

    // Op het e-mailadres i.p.v. de naam: dan houdt iemand dezelfde kleur
    // nadat hij zijn naam wijzigt.
    $background = $palette[abs(crc32((string) $user->email)) % count($palette)];

    // Eerste en laatste woord, niet de eerste twee: anders wordt
    // "Anne de Vries" tot "AD" in plaats van "AV".
    $words = Str::of($user->name)->squish()->explode(' ')->filter()->values();

    $initials = $words->count() > 1
        ? Str::substr($words->first(), 0, 1).Str::substr($words->last(), 0, 1)
        : Str::substr($words->first() ?? '', 0, 1);
@endphp

<span
    {{ $attributes->class([
        'inline-flex shrink-0 select-none items-center justify-center rounded-full font-semibold text-white',
        $sizes[$size] ?? $sizes['md'],
        $background,
    ]) }}
    aria-hidden="true"
>{{ Str::upper($initials) }}</span>
