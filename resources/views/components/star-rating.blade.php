@props(['rating' => 0, 'count' => null, 'size' => 'size-4'])

@php
    $rating = (float) $rating;
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }} role="img" aria-label="{{ number_format($rating, 1) }} out of 5">
    <span class="flex items-center text-pink" aria-hidden="true">
        @for($i = 1; $i <= 5; $i++)
            @if($i <= $full)
                <x-lucide-icon name="star" class="{{ $size }} fill-current" />
            @elseif($half && $i === $full + 1)
                <x-lucide-icon name="star" class="{{ $size }} fill-current opacity-50" />
            @else
                <x-lucide-icon name="star" class="{{ $size }} text-ink-faint" />
            @endif
        @endfor
    </span>
    @if($count !== null)
        <span class="text-xs text-ink-soft">({{ $count }})</span>
    @endif
</span>
