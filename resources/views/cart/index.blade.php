@php $subtotal = $cart?->subtotal_pence ?? 0; $threshold = App\Support\Delivery::freeThresholdPence(); $remaining = max(0, $threshold - $subtotal); @endphp
<x-layouts.storefront :title="'Your Bag — Cardyshub'">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="font-display text-4xl text-ink mb-10">Your bag</h1>

        @if($items->isEmpty())
            <x-empty-state icon="shopping-bag" heading="Your bag is empty" :cta-href="route('shop.index')" cta-label="Shop all">
                Start with a cleanser, or let us build your routine.
            </x-empty-state>
        @else
            <div class="mb-8 bg-pink-wash rounded-card p-4" data-free-delivery>
                @if($remaining > 0)
                    <p class="text-sm text-ink mb-2" data-free-delivery-message>Add <strong>{{ money($remaining) }}</strong> more for free UK delivery.</p>
                @else
                    <p class="text-sm text-ink mb-2 font-medium" data-free-delivery-message>You've unlocked free UK delivery. 🎉</p>
                @endif
                <div class="h-1.5 rounded-pill bg-white overflow-hidden">
                    <div class="h-full bg-pink transition-all duration-500" data-free-delivery-bar style="width: {{ min(100, ($subtotal / $threshold) * 100) }}%"></div>
                </div>
            </div>

            <div class="divide-y divide-line border-t border-b border-line">
                @foreach($items as $item)
                    {{-- Stacks on phones, single row from sm up.
                         It used to be one flex row at every width: thumbnail,
                         name, quantity, line total and remove all competing for
                         288px at 320. The fixed-width controls won and the name
                         column collapsed to 0px, so the cart showed a picture
                         and a price with no product name on every phone. --}}
                    <div class="cart-row flex items-start gap-3 sm:gap-4 py-5" data-cart-item="{{ $item->id }}">
                        <a href="{{ route('shop.show', $item->product) }}" class="h-20 w-20 rounded-lg bg-white border border-line overflow-hidden shrink-0">
                            @if($item->product->images->first())
                                <img src="{{ asset('storage/'.$item->product->images->first()->path) }}" alt="" class="h-full w-full object-contain p-2">
                            @endif
                        </a>

                        <div class="flex-1 min-w-0 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                            <div class="min-w-0 sm:flex-1">
                                <a href="{{ route('shop.show', $item->product) }}" class="block font-medium text-ink hover:text-pink transition">{{ $item->product->name }}</a>
                                @if($item->variant)
                                    <p class="text-sm text-ink-soft">{{ $item->variant->name }}</p>
                                @endif
                                <p class="text-sm text-ink-soft mt-1 font-mono">{{ money($item->price_pence) }} each</p>
                            </div>

                            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                                {{-- requestSubmit(), not submit(): submit() skips the
                                     submit event, and with it the AJAX handler. --}}
                                <form method="POST" action="{{ route('cart.update', $item) }}" data-cart-form>
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="qty-{{ $item->id }}">Quantity for {{ $item->product->name }}</label>
                                    <input id="qty-{{ $item->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="20"
                                           inputmode="numeric"
                                           onchange="this.form.requestSubmit()"
                                           class="w-16 h-11 rounded border-line text-sm text-center focus:border-pink focus:ring-pink">
                                    <button type="submit" class="sr-only">Update quantity</button>
                                </form>

                                <p class="flex-1 sm:w-24 text-right font-mono font-medium text-ink" data-cart-line-total>{{ money($item->line_total_pence) }}</p>

                                <form method="POST" action="{{ route('cart.remove', $item) }}" data-cart-form>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tap-target text-ink-faint hover:text-alert transition">
                                        <span class="sr-only">Remove {{ $item->product->name }}</span>
                                        <x-lucide-icon name="x" class="size-5" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <div class="w-full sm:w-80 space-y-4">
                    <div class="flex justify-between text-lg font-display text-ink">
                        <span>Subtotal</span>
                        <span class="font-mono" data-cart-subtotal>{{ money($subtotal) }}</span>
                    </div>
                    <p class="text-xs text-ink-soft">Shipping and discounts calculated at checkout.</p>
                    <x-button tag="a" href="{{ route('checkout.index') }}" variant="primary" size="lg" class="w-full">Checkout</x-button>
                </div>
            </div>
        @endif
    </div>

</x-layouts.storefront>
