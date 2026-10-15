@props(['eyebrow' => null, 'viewAllHref' => null, 'viewAllLabel' => 'View all', 'align' => 'left'])

<div class="flex items-end justify-between gap-4 mb-8 sm:mb-10 {{ $align === 'center' ? 'flex-col text-center' : '' }}" data-reveal-child>
    <div>
        @if($eyebrow)
            <p class="font-mono text-xs uppercase tracking-[0.08em] text-pink mb-2">{{ $eyebrow }}</p>
        @endif
        <h2 class="font-display text-3xl sm:text-4xl text-ink">{{ $slot }}</h2>
    </div>
    @if($viewAllHref)
        <a href="{{ $viewAllHref }}" class="tap-inline shrink-0 text-sm font-medium text-ink border-b border-transparent hover:border-pink transition">
            {{ $viewAllLabel }} &rarr;
        </a>
    @endif
</div>
