<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\Delivery;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function currentCart(bool $create = true): ?Cart
    {
        if (Auth::check()) {
            $cart = Cart::firstOrNew(['user_id' => Auth::id()]);
        } else {
            $cart = Cart::firstOrNew(['session_id' => session()->getId(), 'user_id' => null]);
        }

        if (! $cart->exists && $create) {
            $cart->save();
        }

        if (! Auth::check() && $cart->exists) {
            // Session IDs are regenerated on login (session-fixation protection), so the
            // guest cart's session_id is stashed as session *data*, which survives that
            // regeneration, letting mergeGuestCartIntoUser() find it after login.
            session()->put('guest_cart_session_id', $cart->session_id);
        }

        return $cart->exists ? $cart : null;
    }

    public function addItem(Product $product, ?ProductVariant $variant, int $quantity = 1): CartItem
    {
        $cart = $this->currentCart();

        $pricePence = $variant?->effective_price_pence ?? $product->price_pence;

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($item) {
            $item->update(['quantity' => $item->quantity + $quantity]);
        } else {
            $item = $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $quantity,
                'price_pence' => $pricePence,
            ]);
        }

        return $item;
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function mergeGuestCartIntoUser(User $user): void
    {
        $guestSessionId = session()->pull('guest_cart_session_id');

        if (! $guestSessionId) {
            return;
        }

        $guestCart = Cart::where('session_id', $guestSessionId)->whereNull('user_id')->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($guestCart->items as $guestItem) {
            $existing = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $guestItem->quantity]);
            } else {
                $userCart->items()->create([
                    'product_id' => $guestItem->product_id,
                    'product_variant_id' => $guestItem->product_variant_id,
                    'quantity' => $guestItem->quantity,
                    'price_pence' => $guestItem->price_pence,
                ]);
            }
        }

        $guestCart->delete();
    }

    public function itemCount(): int
    {
        $cart = $this->currentCart(create: false);

        return $cart ? $cart->items()->sum('quantity') : 0;
    }

    /**
     * Everything the storefront renders about the cart, in one array.
     *
     * The JSON cart responses hand this straight to the browser, so the badge,
     * the line totals, the subtotal and the free-delivery bar are all worked
     * out here. No pricing rule gets a second implementation in JavaScript.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $cart = $this->currentCart(create: false);
        $items = $cart ? $cart->items()->get() : collect();

        $subtotal = (int) $items->sum(fn (CartItem $item) => $item->price_pence * $item->quantity);
        $threshold = Delivery::freeThresholdPence();
        $remaining = max(0, $threshold - $subtotal);

        return [
            'count' => (int) $items->sum('quantity'),
            'subtotal' => money($subtotal),
            'free_delivery' => [
                'qualified' => $remaining === 0,
                'remaining' => money($remaining),
                'progress' => $threshold > 0 ? min(100, round($subtotal / $threshold * 100, 2)) : 100,
            ],
            'items' => $items->map(fn (CartItem $item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'line_total' => money($item->line_total_pence),
            ])->all(),
        ];
    }
}
