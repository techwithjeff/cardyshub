<x-layouts.storefront :title="'Order Confirmation — Cardyshub'">

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        @if($order->status === 'paid' || $order->paid_at)
            <div class="size-14 rounded-full bg-mint/10 flex items-center justify-center mx-auto mb-6">
                <x-lucide-icon name="check-circle-2" class="size-7 text-mint" />
            </div>
            <h1 class="font-display text-3xl text-ink">Thank you, your order is confirmed!</h1>
            <p class="text-ink-soft mt-3">Order #{{ $order->order_number }} &middot; <span class="font-mono">{{ money($order->total_pence) }}</span></p>
            <p class="text-ink-faint mt-1 text-sm">A confirmation has been sent to {{ $order->email }}.</p>
        @else
            <h1 class="font-display text-3xl text-ink">We're finalising your payment…</h1>
            <p class="text-ink-soft mt-3">Order #{{ $order->order_number }}. This page will update shortly — refresh if the status doesn't change in a minute.</p>
        @endif

        <div class="mt-10 text-left border-t border-line pt-8">
            <h2 class="font-mono text-xs uppercase tracking-[0.06em] text-ink-soft mb-4">Order summary</h2>
            <div class="divide-y divide-line">
                @foreach($order->items as $item)
                    <div class="flex justify-between py-3 text-sm">
                        <span>{{ $item->product_name }} @if($item->variant_name)({{ $item->variant_name }})@endif &times; {{ $item->quantity }}</span>
                        <span class="font-mono font-medium">{{ money($item->line_total_pence) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-sm mt-3">
                <span>Shipping</span>
                <span class="font-mono">{{ $order->shipping_pence > 0 ? money($order->shipping_pence) : 'Free' }}</span>
            </div>
            @if($order->discount_pence > 0)
                <div class="flex justify-between text-sm mt-1">
                    <span>Discount</span>
                    <span class="font-mono">-{{ money($order->discount_pence) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-base font-display mt-3 pt-3 border-t border-line">
                <span>Total</span>
                <span class="font-mono">{{ money($order->total_pence) }}</span>
            </div>
        </div>

        <x-button tag="a" href="{{ route('shop.index') }}" variant="primary" class="mt-10">Continue shopping</x-button>
    </div>

</x-layouts.storefront>
