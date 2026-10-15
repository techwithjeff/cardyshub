<x-layouts.storefront :title="$brand->name.' — Cardyshub'">

    <div class="bg-pink-wash">
        <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <nav aria-label="Breadcrumb" class="breadcrumb text-sm text-ink-soft mb-6">
                <a href="{{ route('home') }}" class="hover:text-pink">Home</a> &rsaquo;
                <a href="{{ route('brands.index') }}" class="hover:text-pink">Brands</a> &rsaquo;
                <span class="text-ink">{{ $brand->name }}</span>
            </nav>
            <div class="flex items-center gap-5">
                <div class="size-16 rounded-full bg-white flex items-center justify-center shrink-0 overflow-hidden">
                    @if($brand->logo)
                        <img src="{{ asset('storage/'.$brand->logo) }}" alt="{{ $brand->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="font-display text-xl text-pink">{{ Str::of($brand->name)->substr(0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <h1 class="font-display text-3xl sm:text-4xl text-ink">{{ $brand->name }}</h1>
                    @if($brand->description)
                        <p class="text-ink-soft mt-1 max-w-2xl">{{ $brand->description }}</p>
                    @else
                        <p class="text-ink-soft mt-1">
                            {{ $products->total() ?: 'No' }} {{ Str::plural('product', $products->total()) }} in stock
                        </p>
                    @endif
                </div>
            </div>

            {{-- Walk the A-Z without going back to the index --}}
            <div class="flex items-center justify-between gap-4 mt-8 pt-6 border-t border-line">
                <div class="flex-1 min-w-0">
                    @if($previous)
                        <a href="{{ route('brands.show', $previous) }}" class="group inline-flex items-center gap-2 text-sm text-ink-soft hover:text-pink transition">
                            <x-lucide-icon name="chevron-right" class="size-4 rotate-180" />
                            <span class="line-clamp-2">{{ $previous->name }}</span>
                        </a>
                    @endif
                </div>
                <a href="{{ route('brands.index') }}" class="text-sm font-medium text-ink hover:text-pink transition shrink-0">All brands</a>
                <div class="flex-1 min-w-0 text-right">
                    @if($next)
                        <a href="{{ route('brands.show', $next) }}" class="group inline-flex items-center gap-2 text-sm text-ink-soft hover:text-pink transition">
                            <span class="line-clamp-2">{{ $next->name }}</span>
                            <x-lucide-icon name="chevron-right" class="size-4" />
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-wrap mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        @if($products->isEmpty())
            <x-empty-state icon="shopping-bag" heading="{{ $brand->name }} is landing soon" :cta-href="route('brands.index')" cta-label="Browse all brands">
                We stock {{ $brand->name }}, but nothing from the range is listed yet. In the meantime, plenty of other brands are ready to shop.
            </x-empty-state>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-10" data-reveal-section>
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-12">{{ $products->links() }}</div>
        @endif
    </div>

</x-layouts.storefront>
