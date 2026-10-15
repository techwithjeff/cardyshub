<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Discount;
use App\Models\Product;
use App\Services\OrderService;
use App\Support\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_totals_are_calculated_correctly_with_free_shipping_over_threshold(): void
    {
        $product = Product::create([
            'name' => 'Serum', 'slug' => 'serum', 'price_pence' => 2000, 'stock_quantity' => 10, 'status' => 'published',
        ]);

        $cart = Cart::create();
        // Six units clears the configured free-delivery threshold at any sane setting.
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 6, 'price_pence' => 2000]);

        $order = app(OrderService::class)->createFromCart($cart, [
            'full_name' => 'Jane Doe', 'line1' => '1 Test St', 'city' => 'London', 'postcode' => 'AB1 2CD', 'country' => 'GB',
        ], 'jane@example.com', null);

        // Subtotal clears the free-delivery threshold, so shipping is zero.
        $this->assertGreaterThanOrEqual(Delivery::freeThresholdPence(), $order->subtotal_pence);
        $this->assertSame(12000, $order->subtotal_pence);
        $this->assertSame(0, $order->shipping_pence);
        $this->assertSame(12000, $order->total_pence);
    }

    public function test_totals_include_shipping_below_threshold_and_apply_discount(): void
    {
        $product = Product::create([
            'name' => 'Balm', 'slug' => 'balm', 'price_pence' => 1000, 'stock_quantity' => 10, 'status' => 'published',
        ]);

        $cart = Cart::create();
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'price_pence' => 1000]);

        $discount = Discount::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);

        $order = app(OrderService::class)->createFromCart($cart, [
            'full_name' => 'Jane Doe', 'line1' => '1 Test St', 'city' => 'London', 'postcode' => 'AB1 2CD', 'country' => 'GB',
        ], 'jane@example.com', null, $discount);

        // Subtotal £10.00 is below the threshold, so the standard rate applies.
        $shipping = Delivery::standardRatePence();
        $this->assertLessThan(Delivery::freeThresholdPence(), $order->subtotal_pence);
        $this->assertSame(1000, $order->subtotal_pence);
        $this->assertSame(100, $order->discount_pence);
        $this->assertSame($shipping, $order->shipping_pence);
        $this->assertSame(900 + $shipping, $order->total_pence);
    }
}
