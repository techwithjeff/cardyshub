<?php

namespace App\Services;

use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Delivery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    public function createFromCart(Cart $cart, array $shippingAddress, string $email, ?string $phone, ?Discount $discount = null, ?string $shippingMethodCode = null): Order
    {
        return DB::transaction(function () use ($cart, $shippingAddress, $email, $phone, $discount, $shippingMethodCode) {
            $items = $cart->items()->with('product', 'variant')->get();

            // The price is taken from the method here, never from the request.
            // An unknown code resolves to the standard service, so editing the
            // form field can only ever change which service you get, not what
            // it costs.
            $method = Delivery::method($shippingMethodCode) ?? Delivery::standardMethod();

            $subtotalPence = $items->sum(fn ($item) => $item->price_pence * $item->quantity);
            $discountPence = $discount ? $discount->calculateDiscountPence($subtotalPence) : 0;
            $shippingPence = $method->pricePenceFor($subtotalPence);
            $totalPence = max(0, $subtotalPence - $discountPence) + $shippingPence;

            $order = Order::create([
                'user_id' => Auth::id(),
                'cart_id' => $cart->id,
                'discount_id' => $discount?->id,
                'email' => $email,
                'phone' => $phone,
                'status' => 'pending',
                'currency' => 'GBP',
                'subtotal_pence' => $subtotalPence,
                'discount_pence' => $discountPence,
                'shipping_pence' => $shippingPence,
                'shipping_method' => $method->code,
                'shipping_method_name' => $method->name,
                'total_pence' => $totalPence,
                'shipping_address' => $shippingAddress,
                'billing_address' => $shippingAddress,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'unit_price_pence' => $item->price_pence,
                    'quantity' => $item->quantity,
                    'line_total_pence' => $item->price_pence * $item->quantity,
                ]);
            }

            return $order;
        });
    }

    public function markPaid(Order $order): void
    {
        if ($order->status === 'paid' || $order->paid_at) {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $item->variant?->decrement('stock_quantity', $item->quantity);
                } elseif ($item->product_id) {
                    Product::where('id', $item->product_id)->decrement('stock_quantity', $item->quantity);
                }
            }

            if ($order->discount_id) {
                $order->discount?->increment('used_count');
            }

            $order->cart?->items()->delete();
        });

        // Best effort, deliberately.
        //
        // By this point the payment has succeeded and the order is committed.
        // markPaid() runs on the customer's confirmation page and in the Stripe
        // webhook, and QUEUE_CONNECTION=sync means this send happens inline. A
        // mail server that is down, misconfigured, or still on placeholder
        // credentials would otherwise throw here and turn the "your payment
        // worked" page into a 500 — the worst possible moment for one — and
        // make Stripe retry a webhook for an order that is already settled.
        //
        // The failure is logged rather than swallowed, so a shop whose mail is
        // broken can still find out. Stripe sends its own receipt regardless.
        $order->load('items');

        try {
            Mail::to($order->email)->send(new OrderConfirmationMail($order));
        } catch (\Throwable $e) {
            Log::error('Order confirmation email could not be sent', [
                'order_number' => $order->order_number,
                'email' => $order->email,
                'error' => $e->getMessage(),
            ]);
        }

        $this->notifyOwnerOfNewOrder($order);
    }

    /**
     * Tell the shop owner an order has come in.
     *
     * Separate from the customer confirmation and separately guarded: one
     * failing must not stop the other, and neither may break the page the
     * customer is looking at. Blank setting means the alerts are switched off,
     * which is a legitimate choice rather than a misconfiguration.
     */
    protected function notifyOwnerOfNewOrder(Order $order): void
    {
        $recipient = trim((string) Setting::get('order_notification_email', ''));

        if ($recipient === '') {
            return;
        }

        try {
            Mail::to($recipient)->send(new NewOrderNotificationMail($order));
        } catch (\Throwable $e) {
            Log::error('New order notification could not be sent to the owner', [
                'order_number' => $order->order_number,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
