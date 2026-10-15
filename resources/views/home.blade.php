@php
    $heroSlides = [
        [
            'theme' => 'pink',
            'eyebrowStyle' => 'serif',
            'eyebrow' => 'Let your skin do the talking',
            'heading' => $heroHeading,
            'subheading' => $heroSubheading,
            'ctaLabel' => 'Shop the range',
            'ctaHref' => route('shop.index'),
            // "Find your routine" has no Routine Builder to point at yet, so it
            // lands on Shop by concern — the nearest thing that exists. Swap the
            // href once the builder is built.
            'secondaryLabel' => 'Find your routine',
            'secondaryHref' => '#concerns',
            // Shown from lg up, in place of the floating product PNGs below.
            'photo' => [
                'srcset' => [
                    1024 => 'images/hero/hero-products-1024.webp',
                    1400 => 'images/hero/hero-products-1400.webp',
                    1717 => 'images/hero/hero-products-1717.webp',
                ],
                'w' => 1717,
                'h' => 916,
            ],
        ],
        [
            'theme' => 'sage',
            'eyebrowStyle' => 'mono',
            'eyebrow' => 'Four steps. No guesswork.',
            'heading' => 'Stop starting over',
            // Deck ended this on "Build a routine that makes sense in under two
            // minutes". Cut, because nothing on the site takes two minutes yet.
            // Restore it when the Routine Builder ships.
            'subheading' => 'Most of us buy the product and then wonder what to do with it. Every Cardyshub order tells you what it is for, when to use it, and what to put on before and after.',
            'ctaLabel' => 'Build my routine',
            'ctaHref' => '#concerns',
            'secondaryLabel' => 'How it works',
            'secondaryHref' => route('about') . '#how-we-work',
            'photo' => [
                'srcset' => [
                    1024 => 'images/hero/hero-body-1024.webp',
                    1400 => 'images/hero/hero-body-1400.webp',
                    1717 => 'images/hero/hero-body-1717.webp',
                ],
                'w' => 1717,
                'h' => 915,
            ],
        ],
    ];
@endphp
<x-layouts.storefront>

    <x-hero-slider :slides="$heroSlides" />

    {{-- Trust strip --}}
    <section class="border-b border-line bg-white" data-reveal-section>
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
            @foreach([
                ['icon' => 'truck', 'label' => 'UK next-day delivery, Mon to Fri'],
                ['icon' => 'rotate-ccw', 'label' => '14-day returns on unopened items'],
                ['icon' => 'file-text', 'label' => 'Full ingredients listed'],
                ['icon' => 'shield-check', 'label' => 'Secure checkout'],
            ] as $item)
                <div class="flex items-center gap-3" data-reveal-child>
                    <x-lucide-icon name="{{ $item['icon'] }}" class="size-5 text-pink shrink-0" />
                    <span class="text-xs sm:text-sm text-ink-soft">{{ $item['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Shop by category --}}
    <section class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24" data-reveal-section>
        <x-section-heading eyebrow="Category">Find your starting point</x-section-heading>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            @foreach($categories as $category)
                <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                   class="group flex flex-col items-center text-center bg-white border border-line rounded-card px-4 py-8 sm:py-10 hover:border-pink hover:shadow-lift transition-all duration-300" data-reveal-child>
                    <div class="size-14 sm:size-16 rounded-full bg-pink-wash flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110 group-hover:bg-pink group-hover:[&>svg]:text-white">
                        <x-lucide-icon name="{{ \App\Http\Controllers\HomeController::CATEGORY_ICONS[$category->slug] ?? 'sparkles' }}" class="size-6 sm:size-7 text-pink transition-colors duration-300" />
                    </div>
                    <p class="font-medium text-sm sm:text-base text-ink group-hover:text-pink transition">{{ $category->name }}</p>
                    <p class="text-xs text-ink-faint mt-1">{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('product', $category->products_count) }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Bestsellers --}}
    @if($featured->isNotEmpty())
    <section class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24" data-reveal-section>
        <x-section-heading eyebrow="Loved most" :view-all-href="route('shop.index')">What everyone is repurchasing</x-section-heading>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 sm:gap-x-6 gap-y-8 sm:gap-y-10">
            @foreach($featured as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>
    @endif

    {{-- Shop by concern --}}
    @if($concerns->isNotEmpty())
    <section id="concerns" class="bg-pink-wash" data-reveal-section>
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
            <x-section-heading eyebrow="Shop by concern"
                               :view-all-href="$concernTotal > $concerns->count() ? route('shop.index') : null"
                               view-all-label="All {{ $concernTotal }} concerns">What is your skin working on?</x-section-heading>
            <div class="flex flex-wrap gap-2.5 sm:gap-3">
                @foreach($concerns as $concern)
                    <a href="{{ route('shop.index', ['concern' => $concern['label']]) }}"
                       class="tap-inline px-4 sm:px-5 py-2 sm:py-2.5 rounded-pill bg-white border border-line text-sm font-medium text-ink hover:border-pink hover:text-pink hover:shadow-card transition-all duration-200" data-reveal-child>
                        {{ $concern['label'] }}
                        <span class="text-ink-faint font-normal">{{ $concern['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- New in --}}
    @if($newIn->isNotEmpty())
    <section class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24" data-reveal-section>
        <x-section-heading eyebrow="Just landed" :view-all-href="route('shop.index')">New this month</x-section-heading>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 sm:gap-x-6 gap-y-8 sm:gap-y-10">
            @foreach($newIn as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>
    @endif

    {{-- Shop by brand --}}
    @if($brands->isNotEmpty())
    <section class="bg-white border-y border-line" data-reveal-section>
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24">
            <x-section-heading eyebrow="Curated for you" :view-all-href="route('brands.index')" view-all-label="All {{ $brandTotal }} brands">Shop by brand</x-section-heading>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                @foreach($brands as $brand)
                    <a href="{{ route('brands.show', $brand) }}" class="group flex flex-col items-center text-center bg-bone rounded-card p-5 sm:p-6 hover:bg-pink-wash transition-colors duration-300" data-reveal-child>
                        <div class="size-12 sm:size-14 rounded-full bg-white flex items-center justify-center mb-3 overflow-hidden shadow-card transition-transform duration-300 group-hover:scale-110">
                            @if($brand->logo)
                                <img src="{{ asset('storage/'.$brand->logo) }}" alt="{{ $brand->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="font-display text-lg text-pink">{{ \Illuminate\Support\Str::of($brand->name)->substr(0, 1) }}</span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm font-medium text-ink group-hover:text-pink transition line-clamp-2">{{ $brand->name }}</p>
                        <p class="text-xs text-ink-faint mt-1">{{ $brand->products_count }} {{ \Illuminate\Support\Str::plural('product', $brand->products_count) }}</p>
                    </a>
                @endforeach
            </div>

            <p class="text-sm text-ink-soft mt-8 text-center">
                Plus {{ $brandTotal - $brands->count() }} more brands landing soon &mdash;
                <a href="{{ route('brands.index') }}" class="text-pink font-medium hover:underline">see the full A&ndash;Z</a>.
            </p>
        </div>
    </section>
    @endif

    {{-- Reviews --}}
    @php
        // Placeholder testimonials — replace with real, attributable reviews
        // before launch. Six of them so one copy of the marquee track is wider
        // than any viewport, which is what makes the loop seamless.
        $reviews = [
            ['quote' => 'I stopped guessing. A few products, in order, and my skin finally looks even.', 'name' => 'Amara T.', 'rating' => 5],
            ['quote' => 'The instructions on each product are the reason I actually stuck with it this time.', 'name' => 'Jodie M.', 'rating' => 5],
            ['quote' => 'Ordered on Tuesday, arrived Thursday, and the packaging is beautiful.', 'name' => 'Rachel O.', 'rating' => 4],
            ['quote' => 'Two months in and the dark spots on my cheeks have genuinely faded.', 'name' => 'Bisi A.', 'rating' => 5],
            ['quote' => 'Finally a shop that explains what an active does before it sells it to you.', 'name' => 'Chloe W.', 'rating' => 5],
            ['quote' => 'My barrier was wrecked from over-exfoliating. The routine they suggested fixed it.', 'name' => 'Nneka E.', 'rating' => 4],
        ];
    @endphp
    <section class="bg-bone border-y border-line overflow-hidden" data-reveal-section>
        <div class="py-14 sm:py-20 lg:py-24">
            <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8">
                <x-section-heading eyebrow="Real skin" align="center">What customers are saying</x-section-heading>
            </div>

            {{-- Continuous auto-scroll. The track holds two copies of the list and
                 animates to -50%, so it loops without a jump. Pauses on hover or
                 keyboard focus, and falls back to a normal scroller under
                 prefers-reduced-motion. All in site.css. --}}
            <div class="reviews-viewport">
                <div class="reviews-track">
                    @for($copy = 0; $copy < 2; $copy++)
                        @foreach($reviews as $review)
                            <figure class="reviews-card bg-white border border-line rounded-card p-6"
                                    @if($copy === 1) aria-hidden="true" @endif>
                                <x-star-rating :rating="$review['rating']" class="mb-4" />
                                <blockquote class="text-ink mb-4">&ldquo;{{ $review['quote'] }}&rdquo;</blockquote>
                                <figcaption class="text-xs text-ink-soft font-medium">{{ $review['name'] }}, verified purchase</figcaption>
                            </figure>
                        @endforeach
                    @endfor
                </div>
            </div>
        </div>
    </section>

    {{-- Newsletter --}}
    <section id="newsletter" class="newsletter-gradient" data-reveal-section>
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 text-center">
            <h2 class="font-display text-2xl sm:text-3xl text-white mb-2" data-reveal-child>Skincare notes, not spam</h2>
            <p class="text-white/80 mb-8 max-w-md mx-auto" data-reveal-child>Honest advice, early access to new products, and 10% off your first order.</p>
            <form method="POST" action="{{ route('newsletter.store') }}" class="max-w-md mx-auto" data-reveal-child>
                @csrf
                <div class="flex flex-col sm:flex-row gap-2 mb-3">
                    <label for="newsletter-email" class="sr-only">Email address</label>
                    <input id="newsletter-email" type="email" name="email" required placeholder="Email address"
                           class="flex-1 min-h-[44px] rounded-pill border-0 px-5 text-sm focus:ring-2 focus:ring-white">
                    <x-button type="submit" variant="pill" class="!bg-ink hover:!bg-ink/80">Sign me up</x-button>
                </div>
                <label class="flex items-center justify-center gap-2 text-xs text-white/80">
                    <input type="checkbox" name="consent" value="1" required class="size-4 rounded border-white/40 text-ink focus:ring-white">
                    Yes, email me skincare notes and offers. Unsubscribe any time.
                </label>
            </form>
        </div>
    </section>

</x-layouts.storefront>
