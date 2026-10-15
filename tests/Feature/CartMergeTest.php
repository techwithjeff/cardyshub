<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_merges_into_user_cart_on_login(): void
    {
        $product = Product::create([
            'category_id' => Category::create(['name' => 'Serums', 'slug' => 'serums'])->id,
            'name' => 'Test Serum',
            'slug' => 'test-serum',
            'price_pence' => 1999,
            'stock_quantity' => 10,
            'status' => 'published',
        ]);

        $user = User::factory()->create();

        // Add to cart as a guest.
        $this->post(route('cart.add', $product), ['quantity' => 2])
            ->assertRedirect();

        // Log in — the session ID regenerates here (Laravel's session-fixation
        // protection), which is exactly the scenario that broke the naive merge.
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->actingAs($user);

        $response = $this->get(route('cart.index'));
        $response->assertOk();
        $response->assertSee('Test Serum');

        $this->assertDatabaseHas('cart_items', [
            'quantity' => 2,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
        ]);
    }
}
