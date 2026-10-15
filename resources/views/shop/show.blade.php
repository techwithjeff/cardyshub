@php
    $isWishlisted = auth()->check() && in_array($product->id, auth()->user()->wishlistProductIds());
    $accordionItems = collect([
        ['id' => 'description', 'title' => 'Description', 'content' => $product->description ?? '<p>'.e($product->short_description).'</p>'],
        $product->how_to_use ? ['id' => 'how-to-use', 'title' => 'How to use', 'content' => '<p>'.e($product->how_to_use).'</p>'] : null,
        ['id' => 'ingredients', 'title' => 'Full ingredients (INCI)', 'content' => '<p class="text-xs">'.e($product->ingredients_inci).'</p>'],
        // Figures come from App\Support\Delivery and the wording matches the
        // Returns page, which is the canonical version of this policy.
        ['id' => 'delivery', 'title' => 'Delivery & returns', 'content' => '<p>UK next-day delivery Monday to Friday from '.e(\App\Support\Delivery::standardMethod()->price()).', free on orders over '.e(\App\Support\Delivery::freeThreshold()).'. We ship worldwide, quoted by email.</p><p>Tell us within 3 days if your order arrives damaged. You have 14 days from delivery to cancel, and a further 14 days to send unopened items back. Sealed cosmetics cannot be returned once opened, per the hygiene exemption under the Consumer Contracts Regulations 2013.</p><p><a href="'.route('delivery').'" class="text-pink underline">Delivery</a> &middot; <a href="'.route('returns').'" class="text-pink underline">Returns and refunds</a></p>'],
    ])->filter()->values();
@endphp
<x-layouts.storefront :title="$product->name.' — Cardyshub'" :meta-description="$product->short_description">

    <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-10 pb-28 lg:pb-10">
        <nav aria-label="Breadcrumb" class="breadcrumb text-sm text-ink-soft mb-6">
            <a href="{{ route('shop.index') }}" class="hover:text-pink">Shop</a>
            @if($product->category)
                &rsaquo; <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="hover:text-pink">{{ $product->category->name }}</a>
            @endif
            &rsaquo; <span class="text-ink">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- Gallery --}}
            <div class="lg:sticky lg:top-28 self-start">
                <div class="aspect-square rounded-card bg-white border border-line overflow-hidden">
                    @if($product->images->first())
                        <img id="pdp-main-image" src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}"
                             width="700" height="700" class="h-full w-full object-contain p-8">
                    @else
                        <div class="h-full w-full flex items-center justify-center">
                            <img src="{{ asset('brand/icon-colored.svg') }}" alt="" class="h-20 w-20 opacity-30">
                        </div>
                    @endif
                </div>
                @if($product->images->count() > 1)
                    <div class="grid grid-cols-4 gap-3 mt-3">
                        @foreach($product->images as $image)
                            <button type="button" onclick="document.getElementById('pdp-main-image').src = this.querySelector('img').src"
                                    class="aspect-square rounded-lg overflow-hidden bg-white border border-line hover:border-pink transition">
                                <img src="{{ asset('storage/'.$image->path) }}" alt="" class="h-full w-full object-contain p-2">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div>
                @if($product->brand)
                    <a href="{{ route('brands.show', $product->brand) }}" class="tap-inline font-mono text-xs uppercase tracking-[0.06em] text-pink mb-2 hover:underline">{{ $product->brand->name }}</a>
                @elseif($product->category)
                    <p class="font-mono text-xs uppercase tracking-[0.06em] text-pink mb-2">{{ $product->category->name }}</p>
                @endif
                <h1 class="font-display text-3xl sm:text-4xl text-ink mb-3">{{ $product->name }}</h1>

                @if($product->approvedReviews->count())
                    <a href="#reviews" class="inline-block mb-4">
                        <x-star-rating :rating="$product->average_rating" :count="$product->approvedReviews->count()" />
                    </a>
                @endif

                <div class="flex items-center gap-3 mb-1">
                    @if($product->size)
                        <span class="text-sm text-ink-soft">{{ $product->size }}</span>
                    @endif
                </div>
                <x-price :pence="$product->price_pence" :comparePence="$product->compare_at_price_pence" size="xl" class="mb-4" />

                @if($product->key_active)
                    <span class="inline-flex items-center px-3 py-1.5 rounded bg-bone border border-line font-mono text-xs text-ink mb-5">
                        {{ $product->key_active }}
                    </span>
                @endif

                @if($product->short_description)
                    <p class="text-ink-soft mb-6">{{ $product->short_description }}</p>
                @endif

                @auth
                    {{-- Kept outside the add-to-bag form on purpose: HTML has no
                         nested forms, so the browser used to drop this one and
                         the heart submitted the cart form instead. The button
                         stays in the row below and reaches it by `form=`. --}}
                    <form method="POST" action="{{ route('wishlist.toggle', $product) }}" id="pdp-wishlist-form"
                          data-wishlist-form data-product-id="{{ $product->id }}">
                        @csrf
                    </form>
                @endauth

                <form method="POST" action="{{ route('cart.add', $product) }}" class="space-y-4" id="add-to-bag-form" data-cart-form>
                    @csrf

                    @if($product->variants->isNotEmpty())
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1.5">Option</label>
                            <select name="product_variant_id" class="w-full rounded border-line focus:border-pink focus:ring-pink">
                                @foreach($product->variants as $variant)
                                    <option value="{{ $variant->id }}" @disabled($variant->stock_quantity <= 0)>
                                        {{ $variant->name }} @if($variant->stock_quantity <= 0) (Out of stock) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <x-quantity-stepper />
                        @if($product->in_stock)
                            <x-button type="submit" variant="primary" size="lg" class="flex-1">Add to bag</x-button>
                        @else
                            <x-button type="button" variant="secondary" size="lg" class="flex-1" disabled>Out of stock</x-button>
                        @endif
                        @auth
                            <button type="submit" form="pdp-wishlist-form" data-wishlist-toggle
                                    aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
                                    class="size-11 rounded border border-line flex items-center justify-center hover:border-pink transition shrink-0">
                                <span class="sr-only" data-wishlist-label>{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}</span>
                                <x-lucide-icon name="heart" class="size-5 text-ink" />
                            </button>
                        @endauth
                    </div>
                </form>

                <div class="flex flex-wrap gap-x-6 gap-y-2 mt-6 pt-6 border-t border-line">
                    <span class="inline-flex items-center gap-2 text-xs text-ink-soft"><x-lucide-icon name="truck" class="size-4 text-pink" /> UK next-day delivery, Mon to Fri</span>
                    <span class="inline-flex items-center gap-2 text-xs text-ink-soft"><x-lucide-icon name="rotate-ccw" class="size-4 text-pink" /> 14-day returns on unopened items</span>
                    <span class="inline-flex items-center gap-2 text-xs text-ink-soft"><x-lucide-icon name="file-text" class="size-4 text-pink" /> Full ingredients listed</span>
                </div>

                <div class="mt-8">
                    <x-accordion :items="$accordionItems->toArray()" :open-first="true" />
                </div>
            </div>
        </div>

        {{-- More from this brand --}}
        @if($moreFromBrand->isNotEmpty())
        <section class="mt-16 sm:mt-20 lg:mt-28" data-reveal-section>
            <x-section-heading eyebrow="Same brand" :view-all-href="route('brands.show', $product->brand)">More from {{ $product->brand->name }}</x-section-heading>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 sm:gap-x-6 gap-y-8 sm:gap-y-10">
                @foreach($moreFromBrand as $brandProduct)
                    <x-product-card :product="$brandProduct" />
                @endforeach
            </div>
        </section>
        @endif

        {{-- Reviews --}}
        <section id="reviews" class="mt-16 sm:mt-20 lg:mt-28 max-w-3xl" data-reveal-section>
            <x-section-heading eyebrow="Real skin">Reviews</x-section-heading>

            @forelse($product->approvedReviews as $review)
                <div class="border-b border-line py-5" data-reveal-child>
                    <div class="flex items-center justify-between mb-1">
                        <p class="font-medium text-ink">{{ $review->user->name }}</p>
                        <x-star-rating :rating="$review->rating" size="size-3.5" />
                    </div>
                    @if($review->title)
                        <p class="font-medium text-sm mt-1">{{ $review->title }}</p>
                    @endif
                    <p class="text-ink-soft text-sm mt-1">{{ $review->body }}</p>
                </div>
            @empty
                <p class="text-ink-soft text-sm">No reviews yet — be the first to share your thoughts.</p>
            @endforelse

            @auth
                <form method="POST" action="{{ route('reviews.store', $product) }}" class="mt-8 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-ink mb-1.5">Rating</label>
                        <select name="rating" class="rounded border-line focus:border-pink focus:ring-pink">
                            @foreach([5,4,3,2,1] as $rating)
                                <option value="{{ $rating }}">{{ $rating }} star{{ $rating > 1 ? 's' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-form.input label="Title (optional)" name="title" />
                    <div>
                        <label class="block text-sm font-medium text-ink mb-1.5">Your review</label>
                        <textarea name="body" rows="4" required class="w-full rounded border-line focus:border-pink focus:ring-pink"></textarea>
                    </div>
                    <x-button type="submit" variant="primary">Submit review</x-button>
                </form>
            @else
                <p class="text-sm text-ink-soft mt-6"><a href="{{ route('login') }}" class="text-pink hover:underline">Log in</a> to leave a review.</p>
            @endauth
        </section>

        @if($related->isNotEmpty())
        <section class="mt-16 sm:mt-20 lg:mt-28" data-reveal-section>
            <x-section-heading eyebrow="Keep exploring">You may also like</x-section-heading>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 sm:gap-x-6 gap-y-8 sm:gap-y-10">
                @foreach($related as $relatedProduct)
                    <x-product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </section>
        @endif
    </div>

    {{-- Sticky mobile add-to-bag bar --}}
    <div x-data="{ visible: false }"
         x-init="const obs = new IntersectionObserver((e) => visible = !e[0].isIntersecting, { threshold: 0 }); obs.observe(document.getElementById('add-to-bag-form'))"
         x-show="visible" x-cloak x-transition
         class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-line px-4 py-3 flex items-center gap-4 shadow-drawer">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-ink truncate">{{ $product->name }}</p>
            <x-price :pence="$product->price_pence" :comparePence="$product->compare_at_price_pence" size="sm" />
        </div>
        <x-button onclick="document.getElementById('add-to-bag-form').requestSubmit()" variant="primary" :disabled="!$product->in_stock">
            {{ $product->in_stock ? 'Add to bag' : 'Sold out' }}
        </x-button>
    </div>

</x-layouts.storefront>
