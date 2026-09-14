@props(['user', 'size' => 'md'])

@php
    $sizes = [
        'xs' => ['box' => 'h-6 w-6', 'text' => 'text-xs', 'glyph' => 'text-sm'],
        'sm' => ['box' => 'h-8 w-8', 'text' => 'text-xs', 'glyph' => 'text-base'],
        'md' => ['box' => 'h-10 w-10', 'text' => 'text-sm', 'glyph' => 'text-xl'],
        'lg' => ['box' => 'h-16 w-16', 'text' => 'text-xl', 'glyph' => 'text-3xl'],
        'xl' => ['box' => 'h-24 w-24', 'text' => 'text-3xl', 'glyph' => 'text-5xl'],
    ];

    $dimensions = $sizes[$size] ?? $sizes['md'];

    $symbol = $user->avatarSymbol();
    $glyph = $symbol->glyph();

    // Het symbool wordt als tekst getekend, dus het schaalt mee met de
    // lettergrootte; de initialen staan er wat kleiner in dan een emoji.
    $scale = $glyph === '' ? $dimensions['text'] : $dimensions['glyph'];
@endphp

<span
    {{ $attributes->class([
        'inline-flex shrink-0 select-none items-center justify-center rounded-full font-semibold leading-none text-white',
        $dimensions['box'],
        $scale,
        $user->avatarColor()->backgroundClass(),
        $user->avatarFrame()->frameClass(),
    ]) }}
    aria-hidden="true"
>{{ $glyph !== '' ? $glyph : $user->initials() }}</span>
