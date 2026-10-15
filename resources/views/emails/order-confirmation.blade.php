<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; background: #ffffff; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 0 auto; padding: 32px 24px; }
        .brand { color: #fa0467; font-weight: bold; font-size: 20px; letter-spacing: 0.05em; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        td { padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
        .total { font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <p class="brand">CARDYSHUB</p>
        <h2>Thank you for your order, {{ $order->shipping_address['full_name'] ?? '' }}!</h2>
        <p>Your order <strong>#{{ $order->order_number }}</strong> has been confirmed.</p>

        <table>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }} @if($item->variant_name)({{ $item->variant_name }})@endif &times; {{ $item->quantity }}</td>
                    <td style="text-align:right">{{ money($item->line_total_pence) }}</td>
                </tr>
            @endforeach
            <tr>
                <td>Shipping</td>
                <td style="text-align:right">{{ $order->shipping_pence > 0 ? money($order->shipping_pence) : 'Free' }}</td>
            </tr>
            <tr class="total">
                <td>Total</td>
                <td style="text-align:right">{{ money($order->total_pence) }}</td>
            </tr>
        </table>

        <p style="margin-top:24px; font-size:14px; color:#6b7280;">
            @if($order->isPickup())
                Collect from: {{ App\Support\Delivery::pickupAddress() }}. We will email you as soon as it is ready.
            @else
                Shipping to: {{ implode(', ', array_slice($order->shippingAddressLines(), 1)) }}
            @endif
        </p>

        <p style="margin-top:32px; font-size:13px; color:#9ca3af;">Let your skin do the talking &mdash; Cardyshub.uk</p>
    </div>
</body>
</html>
