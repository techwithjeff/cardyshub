{{--
    Owner-facing order notification. Written to be acted on, not admired:
    what was bought, where it goes, which service to post it by, and a link
    into the admin. Inline styles throughout, because email clients strip
    stylesheets.
--}}
@php
    $ship = $order->shipping_address ?? [];
    $adminUrl = rtrim(config('app.url'), '/').'/admin/orders/'.$order->id;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; background: #f7f5f4; margin: 0; padding: 0; }
        .container { max-width: 560px; margin: 0 auto; padding: 28px 24px; background: #ffffff; }
        .brand { color: #fa0467; font-weight: bold; font-size: 18px; letter-spacing: 0.05em; margin: 0 0 4px; }
        h2 { font-size: 20px; margin: 8px 0 4px; }
        .meta { color: #6b7280; font-size: 13px; margin: 0 0 20px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .total td { font-weight: bold; font-size: 16px; border-bottom: none; padding-top: 12px; }
        .right { text-align: right; white-space: nowrap; }
        .panel { background: #fff0f6; border-radius: 8px; padding: 14px 16px; margin-top: 20px; font-size: 14px; }
        .panel h3 { margin: 0 0 6px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.06em; color: #c40350; }
        .btn { display: inline-block; margin-top: 22px; background: #c40350; color: #ffffff !important;
               text-decoration: none; padding: 11px 20px; border-radius: 100px; font-size: 14px; font-weight: bold; }
        .foot { margin-top: 26px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <p class="brand">CARDYSHUB</p>
        <h2>New order #{{ $order->order_number }}</h2>
        <p class="meta">
            {{ $order->paid_at?->format('d M Y \a\t H:i') ?? now()->format('d M Y \a\t H:i') }}
            &middot; paid &middot; {{ money($order->total_pence) }}
        </p>

        <table>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product_name }}@if($item->variant_name) ({{ $item->variant_name }})@endif
                        <br><span style="color:#6b7280; font-size:12px;">
                            Qty {{ $item->quantity }}@if($item->sku) &middot; SKU {{ $item->sku }}@endif
                        </span>
                    </td>
                    <td class="right">{{ money($item->line_total_pence) }}</td>
                </tr>
            @endforeach

            <tr>
                <td>Subtotal</td>
                <td class="right">{{ money($order->subtotal_pence) }}</td>
            </tr>

            @if($order->discount_pence > 0)
                <tr>
                    <td>Discount</td>
                    <td class="right">&minus;{{ money($order->discount_pence) }}</td>
                </tr>
            @endif

            <tr>
                <td>Delivery{{ $order->shipping_method_name ? ' — '.$order->shipping_method_name : '' }}</td>
                <td class="right">{{ $order->shipping_pence > 0 ? money($order->shipping_pence) : 'Free' }}</td>
            </tr>

            <tr class="total">
                <td>Total paid</td>
                <td class="right">{{ money($order->total_pence) }}</td>
            </tr>
        </table>

        <div class="panel">
            {{-- Not "Post to" on a collection order: the whole point is that --}}
            {{-- nothing goes in the post. --}}
            <h3>{{ $order->isPickup() ? 'Collection — do not post' : 'Post to' }}</h3>
            {!! implode('<br>', array_map('e', $order->shippingAddressLines())) !!}
            @if($order->shipping_method_name)
                <br><br><strong>Service:</strong> {{ $order->shipping_method_name }}
            @endif
        </div>

        <div class="panel" style="background:#f9fafb;">
            <h3 style="color:#6b7280;">Customer</h3>
            {{ $order->email }}
            @if($order->phone)<br>{{ $order->phone }}@endif
            @if(! empty($ship['phone']) && $ship['phone'] !== $order->phone)<br>{{ $ship['phone'] }}@endif
            <br><span style="color:#6b7280; font-size:12px;">Reply to this email to contact them.</span>
        </div>

        @if($order->notes)
            <div class="panel" style="background:#fffbeb;">
                <h3 style="color:#92400e;">Order note</h3>
                {{ $order->notes }}
            </div>
        @endif

        <a href="{{ $adminUrl }}" class="btn">Open in admin</a>

        <p class="foot">Sent automatically by cardyshub.com when an order is paid for.</p>
    </div>
</body>
</html>
