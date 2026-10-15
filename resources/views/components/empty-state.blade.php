@props(['icon' => 'shopping-bag', 'heading', 'ctaHref' => null, 'ctaLabel' => null])

<div class="flex flex-col items-center justify-center text-center py-16 px-6">
    <div class="size-14 rounded-full bg-pink-wash flex items-center justify-center mb-5">
        <x-lucide-icon name="{{ $icon }}" class="size-6 text-pink" />
    </div>
    <p class="font-display text-xl text-ink mb-2">{{ $heading }}</p>
    <div class="text-sm text-ink-soft max-w-xs mb-6">{{ $slot }}</div>
    @if($ctaHref)
        <x-button tag="a" href="{{ $ctaHref }}" variant="primary">{{ $ctaLabel ?? 'Shop all' }}</x-button>
    @endif
</div>
