<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Order #{{ $order->order_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <span class="text-xs font-medium uppercase px-2 py-1 rounded-full bg-pink-wash text-pink-deep">{{ $order->status }}</span>
                    <span class="text-sm text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <div class="flex justify-between py-3 text-sm">
                            <span>{{ $item->product_name }} @if($item->variant_name)({{ $item->variant_name }})@endif &times; {{ $item->quantity }}</span>
                            <span class="font-medium">{{ money($item->line_total_pence) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-1 text-sm">
                    <div class="flex justify-between"><span>Subtotal</span><span>{{ money($order->subtotal_pence) }}</span></div>
                    @if($order->discount_pence > 0)
                        <div class="flex justify-between"><span>Discount</span><span>-{{ money($order->discount_pence) }}</span></div>
                    @endif
                    <div class="flex justify-between"><span>Shipping</span><span>{{ $order->shipping_pence > 0 ? money($order->shipping_pence) : 'Free' }}</span></div>
                    <div class="flex justify-between font-semibold text-base pt-2 border-t border-gray-100"><span>Total</span><span>{{ money($order->total_pence) }}</span></div>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-2">{{ $order->isPickup() ? 'Collection' : 'Shipping address' }}</h3>
                <p class="text-sm text-gray-700">
                    {!! implode('<br>', array_map('e', $order->shippingAddressLines())) !!}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
