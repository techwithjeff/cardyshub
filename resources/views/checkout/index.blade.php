@php
    $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

    $methods = App\Support\Delivery::methods();
    $selectedMethod = old('shipping_method', App\Support\Delivery::standardMethod()->code);

    // Prices resolved server-side for this basket, so the free-over-threshold
    // rule is applied once, here, and the browser only ever echoes the result.
    $methodPrices = collect($methods)->mapWithKeys(fn ($m) => [$m->code => $m->pricePenceFor($subtotalPence)]);
@endphp
<x-layouts.storefront :title="'Checkout — Cardyshub'">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <a href="{{ route('home') }}" class="tap-target inline-flex items-center mb-10">
            <img src="{{ asset('brand/full-colored.svg') }}" alt="Cardyshub" class="h-9 w-auto">
        </a>

        {{-- One Alpine scope across both columns so choosing a delivery option
             updates the summary total without a round trip. --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12"
             x-data="{
                method: @js($selectedMethod),
                prices: @js($methodPrices),
                subtotal: {{ (int) $subtotalPence }},
                get shipping() { return this.prices[this.method] ?? 0 },
                get total() { return this.subtotal + this.shipping },
                get isPickup() { return this.method === 'pickup' },
                money(pence) { return '£' + (pence / 100).toFixed(2) }
             }">
            <form method="POST" action="{{ route('checkout.store') }}" class="lg:col-span-2 space-y-8">
                @csrf

                <div>
                    <h2 class="font-display text-xl text-ink mb-5" x-text="isPickup ? 'Your details' : 'Contact & delivery'">Contact &amp; delivery</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <x-form.input label="Full name" name="full_name" required :value="old('full_name', $defaultAddress?->full_name ?? auth()->user()?->name)" />
                        </div>
                        <x-form.input label="Email" type="email" name="email" required :value="old('email', auth()->user()?->email)" />
                        <x-form.input label="Phone (optional)" name="phone" :value="old('phone', $defaultAddress?->phone ?? auth()->user()?->phone)" />
                    </div>

                    {{-- Hidden for collection: there is nothing to post, so asking
                         for a delivery address only invites a wrong one.
                         `required` is bound rather than fixed, or the browser
                         would block submission on fields it cannot show. The
                         server applies the same rule via required_unless. --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4" x-show="! isPickup">
                        <div class="sm:col-span-2">
                            <x-form.input label="Address line 1" name="line1" x-bind:required="! isPickup" :value="old('line1', $defaultAddress?->line1)" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-form.input label="Address line 2 (optional)" name="line2" :value="old('line2', $defaultAddress?->line2)" />
                        </div>
                        <x-form.input label="City" name="city" x-bind:required="! isPickup" :value="old('city', $defaultAddress?->city)" />
                        <x-form.input label="Postcode" name="postcode" x-bind:required="! isPickup" :value="old('postcode', $defaultAddress?->postcode)" />
                        <x-form.select label="Country" name="country" :options="['GB' => 'United Kingdom']" />
                    </div>

                    {{-- Shown in its place, so the customer knows where they are
                         travelling to before they pay. --}}
                    <div class="mt-4 rounded-card border border-line bg-pink-wash p-4" x-show="isPickup" x-cloak>
                        <p class="text-sm font-medium text-ink">Collect from</p>
                        <p class="text-sm text-ink-soft mt-1">{{ App\Support\Delivery::pickupAddress() }}</p>
                        <p class="text-xs text-ink-soft mt-2">
                            We will email you as soon as your order is ready, usually within 2 working days.
                            Please wait for that email before travelling.
                        </p>
                    </div>

                    @error('country')
                        <p class="text-sm text-alert mt-2">{{ $message }}</p>
                    @enderror
                    @foreach(['line1', 'city', 'postcode'] as $addressField)
                        @error($addressField)
                            <p class="text-sm text-alert mt-2">{{ $message }}</p>
                        @enderror
                    @endforeach
                    <p class="text-xs text-ink-soft mt-3" x-show="! isPickup">
                        Shipping outside the UK? We deliver worldwide —
                        <a href="{{ route('contact') }}" class="text-pink underline">email us</a> and we will quote your country.
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-xl text-ink mb-1">Delivery</h2>
                    <p class="text-sm text-ink-soft mb-4">Order before {{ App\Support\Delivery::cutOffTime() }}, Monday to Friday, and next-day options arrive tomorrow.</p>

                    {{-- One bordered card with hairline-separated rows, rather than
                         twelve separate boxes: with a list this long, gaps between
                         cards read as clutter on a phone. --}}
                    <div class="shipping-options rounded-card border border-line bg-white overflow-hidden"
                         role="radiogroup" aria-label="Shipping method">
                        @foreach($methods as $method)
                            @php $isFree = $method->isFreeOn($subtotalPence); @endphp
                            <label class="shipping-option"
                                   :class="method === @js($method->code) ? 'is-selected' : ''">
                                <input type="radio" name="shipping_method" value="{{ $method->code }}" x-model="method"
                                       class="shipping-option-radio border-line text-pink-btn focus:ring-pink">
                                <span class="shipping-option-name">{{ $method->name }}</span>
                                <span class="shipping-option-price {{ $isFree ? 'text-mint' : 'text-ink' }}">
                                    {{ $isFree ? 'Free' : $method->price() }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <p class="text-xs text-ink-soft mt-3">
                        Delivery times for each service are on the
                        <a href="{{ route('delivery') }}" class="text-pink underline">delivery page</a>.
                    </p>

                    @error('shipping_method')
                        <p class="text-sm text-alert mt-2">{{ $message }}</p>
                    @enderror

                    @if($subtotalPence < App\Support\Delivery::freeThresholdPence())
                        <p class="text-xs text-ink-soft mt-3">
                            Spend {{ money(App\Support\Delivery::freeThresholdPence() - $subtotalPence) }} more and
                            {{ App\Support\Delivery::standardMethod()->name }} is free.
                        </p>
                    @endif
                </div>

                <div>
                    <x-form.input label="Discount code (optional)" name="discount_code" :value="old('discount_code')" :error="$errors->first('discount_code')" class="sm:w-64" />
                </div>

                <x-button type="submit" variant="primary" size="lg" class="w-full">Continue to payment</x-button>

                <p class="text-xs text-ink-faint text-center flex items-center justify-center gap-1.5">
                    <x-lucide-icon name="shield-check" class="size-4" /> Payment powered by Stripe. We never see your card details.
                </p>
            </form>

            <div>
                <h2 class="font-display text-xl text-ink mb-5">Order summary</h2>
                <div class="divide-y divide-line border-t border-b border-line">
                    @foreach($items as $item)
                        <div class="flex items-center gap-3 py-4">
                            <div class="h-14 w-14 rounded-lg bg-white border border-line overflow-hidden shrink-0">
                                @if($item->product->images->first())
                                    <img src="{{ asset('storage/'.$item->product->images->first()->path) }}" alt="" class="h-full w-full object-contain p-1.5">
                                @endif
                            </div>
                            <div class="flex-1 text-sm">
                                <p class="font-medium text-ink">{{ $item->product->name }}</p>
                                <p class="text-ink-soft">Qty {{ $item->quantity }}</p>
                            </div>
                            <p class="text-sm font-mono font-medium text-ink">{{ money($item->price_pence * $item->quantity) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 space-y-2">
                    <div class="flex justify-between text-sm text-ink-soft">
                        <span>Subtotal</span>
                        <span class="font-mono">{{ money($subtotalPence) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-ink-soft">
                        <span>Delivery</span>
                        <span class="font-mono" x-text="shipping === 0 ? 'Free' : money(shipping)">{{ money($methodPrices[$selectedMethod] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-display text-ink pt-2 border-t border-line">
                        <span>Total</span>
                        <span class="font-mono" x-text="money(total)">{{ money($subtotalPence + ($methodPrices[$selectedMethod] ?? 0)) }}</span>
                    </div>
                </div>
                <p class="text-xs text-ink-soft mt-3">
                    Free {{ App\Support\Delivery::standardMethod()->name }} on UK orders over {{ App\Support\Delivery::freeThreshold() }}.
                    Any discount code is applied on the payment step.
                </p>
            </div>
        </div>
    </div>

</x-layouts.storefront>
