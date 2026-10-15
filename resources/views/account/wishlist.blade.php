<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Wishlist</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if($products->isEmpty())
                <x-empty-state icon="heart" heading="Nothing saved yet" :cta-href="route('shop.index')" cta-label="Shop all">
                    Tap the heart on any product to keep it here.
                </x-empty-state>
            @else
                {{-- data-wishlist-grid tells site.js that un-saving a product
                     here should take its card out of the grid, rather than
                     leaving a heart that no longer matches the page. --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-10" data-wishlist-grid>
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
