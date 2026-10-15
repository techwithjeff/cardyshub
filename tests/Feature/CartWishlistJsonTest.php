<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The JSON half of the cart and wishlist routes, which the storefront's fetch
 * handlers depend on.
 *
 * Each route answers twice over: JSON when the caller asks for it, and the
 * original redirect otherwise. The redirect assertions here are not padding —
 * they are what keeps the forms working with JavaScript off, and they are the
 * easiest thing to break while changing the JSON shape.
 */
class CartWishlistJsonTest extends TestCase
{
    use RefreshDatabase;

    protected function product(string $name = 'Test Serum', int $pricePence = 1999): Product
    {
        return Product::create([
            'category_id' => Category::firstOrCreate(['slug' => 'serums'], ['name' => 'Serums'])->id,
            'name' => $name,
            'slug' => str($name)->slug()->value(),
            'price_pence' => $pricePence,
            'stock_quantity' => 10,
            'status' => 'published',
        ]);
    }

    public function test_adding_to_cart_returns_the_cart_summary_as_json(): void
    {
        $product = $this->product(pricePence: 2500);

        $response = $this->postJson(route('cart.add', $product), ['quantity' => 2]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Test Serum added to your cart.');
        $response->assertJsonPath('cart.count', 2);
        $response->assertJsonPath('cart.subtotal', '£50.00');
        $response->assertJsonPath('cart.free_delivery.qualified', false);
        $response->assertJsonPath('cart.items.0.quantity', 2);
        $response->assertJsonPath('cart.items.0.line_total', '£50.00');
    }

    public function test_adding_to_cart_without_asking_for_json_still_redirects(): void
    {
        $product = $this->product();

        $this->post(route('cart.add', $product), ['quantity' => 1])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 1]);
    }

    public function test_free_delivery_flips_once_the_threshold_is_cleared(): void
    {
        $product = $this->product(pricePence: 10000);

        $response = $this->postJson(route('cart.add', $product), ['quantity' => 1]);

        $response->assertJsonPath('cart.free_delivery.qualified', true);
        $response->assertJsonPath('cart.free_delivery.remaining', '£0.00');
        $response->assertJsonPath('cart.free_delivery.progress', 100);
    }

    public function test_updating_and_removing_report_the_recalculated_cart(): void
    {
        // Signed in, so the cart is keyed on the user rather than on a session
        // id that the test client regenerates between requests.
        $this->actingAs(User::factory()->create());

        $product = $this->product(pricePence: 1000);
        $this->postJson(route('cart.add', $product), ['quantity' => 3]);

        $item = \App\Models\CartItem::firstOrFail();

        $this->patchJson(route('cart.update', $item), ['quantity' => 1])
            ->assertOk()
            ->assertJsonPath('cart.count', 1)
            ->assertJsonPath('cart.subtotal', '£10.00');

        $this->deleteJson(route('cart.remove', $item))
            ->assertOk()
            ->assertJsonPath('cart.count', 0)
            ->assertJsonPath('cart.subtotal', '£0.00')
            ->assertJsonPath('removed_item_id', $item->id);
    }

    public function test_wishlist_toggle_reports_state_and_count_as_json(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $saved = $this->actingAs($user)->postJson(route('wishlist.toggle', $product));
        $saved->assertOk();
        $saved->assertJsonPath('wishlisted', true);
        $saved->assertJsonPath('count', 1);
        $saved->assertJsonPath('product_id', $product->id);
        $saved->assertJsonPath('message', 'Saved to your wishlist.');

        $removed = $this->actingAs($user)->postJson(route('wishlist.toggle', $product));
        $removed->assertJsonPath('wishlisted', false);
        $removed->assertJsonPath('count', 0);
        $removed->assertJsonPath('message', 'Removed from your wishlist.');
    }

    public function test_wishlist_toggle_without_asking_for_json_still_redirects(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)
            ->post(route('wishlist.toggle', $product))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_a_guest_cannot_toggle_a_wishlist_over_json(): void
    {
        $product = $this->product();

        // 401 rather than a redirect, which is the signal site.js uses to hand
        // the request back to the browser as a real navigation to the login page.
        $this->postJson(route('wishlist.toggle', $product))->assertUnauthorized();
    }

    public function test_another_customers_cart_item_cannot_be_touched(): void
    {
        $product = $this->product();
        $this->postJson(route('cart.add', $product), ['quantity' => 1]);
        $item = \App\Models\CartItem::firstOrFail();

        $this->actingAs(User::factory()->create())
            ->patchJson(route('cart.update', $item), ['quantity' => 5])
            ->assertForbidden();
    }
}
