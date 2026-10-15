@props(['pence', 'comparePence' => null, 'size' => 'base'])

@php
    $sizes = ['sm' => 'text-sm', 'base' => 'text-base', 'lg' => 'text-xl', 'xl' => 'text-2xl'];
    $sizeClass = $sizes[$size] ?? $sizes['base'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-baseline gap-2 font-mono']) }}>
    <span class="{{ $sizeClass }} font-semibold {{ $comparePence ? 'text-pink' : 'text-ink' }}">
        {{ money($pence) }}
    </span>
    @if($comparePence)
        <span class="text-sm text-ink-soft line-through">{{ money($comparePence) }}</span>
    @endif
</span>
