@props([
    'variant' => 'primary',
    'size' => 'md',
    'tag' => 'button',
    'loading' => false,
])

@php
    $variants = [
        'primary' => 'bg-pink-btn text-white hover:bg-pink disabled:bg-ink-faint',
        'secondary' => 'bg-transparent border border-ink text-ink hover:bg-ink hover:text-white disabled:border-ink-faint disabled:text-ink-faint',
        'ghost' => 'bg-transparent text-ink border-b border-transparent hover:border-pink',
        'pill' => 'bg-pink-btn text-white hover:bg-pink disabled:bg-ink-faint',
    ];

    // `sm` is h-11 rather than h-9 so every button clears the 44px touch target
    // minimum; site.css enforces the same floor on coarse pointers.
    $sizes = [
        'sm' => 'h-11 px-5 text-sm',
        'md' => 'h-11 px-6 text-sm',
        'lg' => 'h-[52px] px-8 text-base',
    ];

    // `btn` carries the 100px radius and the hover lift, both from site.css.
    $base = 'btn relative inline-flex items-center justify-center gap-2 font-medium disabled:cursor-not-allowed';
    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => $classes, 'type' => $tag === 'button' ? 'submit' : null]) }} @if($loading) aria-busy="true" @endif>
    <span class="inline-flex items-center gap-2 {{ $loading ? 'invisible' : '' }}">
        {{ $slot }}
    </span>
    @if($loading)
        <span class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
            <svg class="animate-spin size-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"></path>
            </svg>
        </span>
    @endif
</{{ $tag }}>
