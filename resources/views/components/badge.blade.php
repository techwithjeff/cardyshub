@props(['variant' => 'new'])

@php
    $variants = [
        'sale' => 'bg-citrus text-ink',
        'new' => 'bg-ink text-white',
        'sold-out' => 'bg-ink-soft text-white',
        'bestseller' => 'bg-pink-wash text-pink border border-pink-line',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold uppercase tracking-wide '.($variants[$variant] ?? $variants['new'])]) }}>
    {{ $slot }}
</span>
