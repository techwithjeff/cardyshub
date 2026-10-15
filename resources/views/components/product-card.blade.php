@props(['product'])

@php
    $images = $product->images;
    $primary = $images->first();
    $secondary = $images->skip(1)->first();
    $badge = null;
    if (! $product->in_stock) {
        $badge = ['variant' => 'sold-out', 'label' => 'Sold out'];
    } elseif ($product->compare_at_price_pence) {
        $badge = ['variant' => 'sale', 'label' => 'Sale'];
    } elseif ($product->created_at && $product->created_at->gt(now()->subDays(21))) {
        $badge = ['variant' => 'new', 'label' => 'New'];
    }
    $inWishlist = auth()->check() && method_exists(auth()->user(), 'wishlistProductIds') ? in_array($product->id, auth()->user()->wishlistProductIds()) : false;
@endphp

<div class="group relative flex flex-col h-full" data-reveal-child data-product-card="{{ $product->id }}">
    <a href="{{ route('shop.show', $product) }}" class="block">
        <div class="relative aspect-square rounded-card border border-line bg-white overflow-hidden">
            @if($primary)
                <img src="{{ asset('storage/'.$primary->path) }}" alt="{{ $primary->alt ?? $product->name }}"
                     width="600" height="600" loading="lazy"
                     class="absolute inset-0 h-full w-full object-contain p-6 transition-opacity duration-300 {{ $secondary ? 'group-hover:opacity-0 group-focus-within:opacity-0' : '' }}">
                @if($secondary)
                    <img src="{{ asset('storage/'.$secondary->path) }}" alt=""
                         width="600" height="600" loading="lazy"
                         class="absolute inset-0 h-full w-full object-contain p-6 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-focus-within:opacity-100">
                @endif
            @else
                <div class="h-full w-full flex items-center justify-center">
                    <img src="{{ asset('brand/icon-colored.svg') }}" alt="" class="h-16 w-16 opacity-30">
                </div>
            @endif

            @if($badge)
                <x-badge variant="{{ $badge['variant'] }}" class="absolute top-3 left-3">{{ $badge['label'] }}</x-badge>
            @endif
        </div>
    </a>

    @auth
        {{-- The saved/unsaved look is driven entirely by aria-pressed (see
             `.wishlist-heart` in site.css), so toggling this one attribute is
             all site.js has to do — and the state stays correct for screen
             readers by construction. --}}
        <form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="absolute top-3 right-3"
              data-wishlist-form data-product-id="{{ $product->id }}">
            @csrf
            <button type="submit" data-wishlist-toggle aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
                    class="wishlist-heart tap-target size-11 rounded-full bg-white/90 backdrop-blur flex items-center justify-center shadow-card">
                <span class="sr-only" data-wishlist-label>{{ $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}</span>
                <x-lucide-icon name="heart" class="size-4 text-ink" />
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="wishlist-heart tap-target absolute top-3 right-3 size-11 rounded-full bg-white/90 backdrop-blur flex items-center justify-center shadow-card">
            <span class="sr-only">Log in to save to wishlist</span>
            <x-lucide-icon name="heart" class="size-4 text-ink" />
        </a>
    @endauth

    <div class="mt-4 flex-1 flex flex-col">
        @if($product->brand)
            <p class="font-mono text-xs uppercase tracking-[0.06em] text-pink mb-1">{{ $product->brand->name }}</p>
        @elseif($product->category)
            <p class="font-mono text-xs uppercase tracking-[0.06em] text-ink-soft mb-1">{{ $product->category->name }}</p>
        @endif
        <a href="{{ route('shop.show', $product) }}" class="font-medium text-sm text-ink line-clamp-2 mb-1.5 hover:text-pink transition">
            {{ $product->name }}
        </a>
        <div class="h-5 mb-2">
            @if($product->approvedReviews->count())
                <x-star-rating :rating="$product->average_rating" :count="$product->approvedReviews->count()" size="size-3.5" />
            @endif
        </div>
        <x-price :pence="$product->price_pence" :comparePence="$product->compare_at_price_pence" class="mb-3" />

        <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-auto" data-cart-form>
            @csrf
            <x-button type="submit" variant="secondary" size="sm" class="w-full" :disabled="!$product->in_stock">
                {{ $product->in_stock ? 'Add to bag' : 'Sold out' }}
            </x-button>
        </form>
    </div>
</div>
