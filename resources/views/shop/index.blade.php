@php
    $activeFilters = collect([
        'category' => $activeCategory?->name,
        'brand' => $activeBrand?->name,
        'concern' => request('concern'),
        'skin_type' => request('skin_type'),
        'min_price' => request('min_price') ? '£'.request('min_price').'+' : null,
        'max_price' => request('max_price') ? 'up to £'.request('max_price') : null,
        'in_stock' => request()->boolean('in_stock') ? 'In stock only' : null,
    ])->filter();
@endphp
<x-layouts.storefront :title="($activeCategory?->name ?? 'Shop').' — Cardyshub'">

    <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

        <nav aria-label="Breadcrumb" class="breadcrumb text-sm text-ink-soft mb-3">
            <a href="{{ route('home') }}" class="hover:text-pink">Home</a> &rsaquo; <span class="text-ink">Shop</span>
        </nav>
        <h1 class="font-display text-3xl sm:text-4xl text-ink mb-2">{{ $activeCategory?->name ?? ($activeBrand?->name ?? 'All products') }}</h1>
        @if($activeCategory?->description)
            <p class="text-ink-soft max-w-2xl mb-6 sm:mb-8">{{ $activeCategory->description }}</p>
        @else
            <p class="text-ink-soft max-w-2xl mb-6 sm:mb-8">Curated skincare and body care from the brands we trust.</p>
        @endif

        <form method="GET" action="{{ route('shop.index') }}" id="shop-filter-form">
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif

            <div class="flex flex-col lg:flex-row gap-8 lg:gap-10">
                <aside class="hidden lg:block lg:w-64 shrink-0">
                    @include('shop._filters')
                </aside>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
                        <p class="text-sm text-ink-soft">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</p>
                        <div class="flex items-center gap-3">
                            <div x-data="{ open: false }" class="lg:hidden">
                                <button type="button" @click="open = true" class="inline-flex items-center gap-2 text-sm font-medium border border-line rounded px-4 py-2">
                                    <x-lucide-icon name="sliders-horizontal" class="size-4" /> Filters
                                </button>
                                <x-drawer open="open" side="left" title="Filters">
                                    <div class="p-5">
                                        @include('shop._filters')
                                    </div>
                                    <x-slot:footer>
                                        <div class="p-4">
                                            <x-button type="button" @click="open = false" variant="primary" class="w-full">Show results</x-button>
                                        </div>
                                    </x-slot:footer>
                                </x-drawer>
                            </div>
                            <select name="sort" onchange="document.getElementById('shop-filter-form').submit()" class="rounded border-line text-sm focus:border-pink focus:ring-pink">
                                <option value="">Newest</option>
                                <option value="price_asc" @selected(request('sort')==='price_asc')>Price: Low to high</option>
                                <option value="price_desc" @selected(request('sort')==='price_desc')>Price: High to low</option>
                                <option value="name" @selected(request('sort')==='name')>Name</option>
                            </select>
                        </div>
                    </div>

                    @if($activeFilters->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-2 mb-6">
                            @foreach($activeFilters as $key => $label)
                                <span class="inline-flex items-center gap-1.5 pl-3 pr-1.5 py-1 rounded-pill bg-pink-wash text-pink text-xs font-medium">
                                    {{ $label }}
                                </span>
                            @endforeach
                            <a href="{{ route('shop.index') }}{{ request('search') ? '?search='.request('search') : '' }}" class="text-xs font-medium text-ink-soft hover:text-pink underline">Clear all</a>
                        </div>
                    @endif

                    @if($products->isEmpty())
                        <x-empty-state icon="sliders-horizontal" heading="No products match those filters" :cta-href="route('shop.index')" cta-label="Clear all filters">
                            Try widening the price range or clearing a filter.
                        </x-empty-state>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-x-4 sm:gap-x-6 gap-y-8 sm:gap-y-10" data-reveal-section>
                            @foreach($products as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>
                        <div class="mt-12">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

</x-layouts.storefront>
