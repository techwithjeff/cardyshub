<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Choosing a delivery service at checkout.
 *
 * The money assertions matter more than the markup ones: shipping is the one
 * price on the site the customer picks, so it is the one a tampered form could
 * try to talk us out of.
 */
class CheckoutShippingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Signed in so the cart is keyed on the user. A guest cart hangs off
        // the session id, which the test client regenerates between requests,
        // so the basket would be empty by the time checkout ran.
        $this->actingAs(User::factory()->create());
    }

    protected function product(int $pricePence = 1999): Product
    {
        return Product::create([
            'category_id' => Category::firstOrCreate(['slug' => 'serums'], ['name' => 'Serums'])->id,
            'name' => 'Test Serum',
            'slug' => 'test-serum',
            'price_pence' => $pricePence,
            'stock_quantity' => 50,
            'status' => 'published',
        ]);
    }

    protected function addToCart(Product $product, int $quantity = 1): void
    {
        $this->post(route('cart.add', $product), ['quantity' => $quantity])->assertRedirect();
    }

    protected function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'line1' => '1 Test Street',
            'city' => 'London',
            'postcode' => 'E1 6AN',
            'country' => 'GB',
            'shipping_method' => Delivery::standardMethod()->code,
        ], $overrides);
    }

    public function test_checkout_offers_every_configured_service(): void
    {
        $this->addToCart($this->product());

        $response = $this->get(route('checkout.index'));
        $response->assertOk();

        // Name and price only, matching the rate sheet. Arrival estimates live
        // on the Delivery page: twelve two-line rows is a long scroll on a phone.
        foreach (Delivery::postedMethods() as $method) {
            $response->assertSee($method->name)->assertSee($method->price(), false);
        }

        // Collection is shown as "Free" rather than "£0", so it is asserted on
        // its own instead of through the price loop above.
        $pickup = Delivery::pickupMethod();
        $response->assertSee($pickup->name)->assertSee('Free');
        $response->assertSee(Delivery::pickupAddress());

        $this->assertCount(12, Delivery::postedMethods());
        $this->assertCount(13, Delivery::methods());
    }

    public function test_the_chosen_service_is_charged_and_recorded(): void
    {
        $this->addToCart($this->product(2000));

        $this->post(route('checkout.store'), $this->checkoutPayload([
            'shipping_method' => 'guaranteed-1pm',
        ]));

        $order = Order::latest('id')->firstOrFail();

        $this->assertSame('guaranteed-1pm', $order->shipping_method);
        $this->assertSame('Guaranteed by 1pm', $order->shipping_method_name);
        $this->assertSame(1025, $order->shipping_pence);
        $this->assertSame(2000 + 1025, $order->total_pence);
    }

    public function test_standard_service_is_free_over_the_threshold(): void
    {
        // £120 basket, comfortably over the £100 threshold.
        $this->addToCart($this->product(12000));

        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::latest('id')->firstOrFail();

        $this->assertSame(0, $order->shipping_pence);
        $this->assertSame(12000, $order->total_pence);
    }

    public function test_an_upgrade_is_still_charged_on_a_large_basket(): void
    {
        $this->addToCart($this->product(12000));

        $this->post(route('checkout.store'), $this->checkoutPayload([
            'shipping_method' => 'signed-1st',
        ]));

        $order = Order::latest('id')->firstOrFail();

        $this->assertSame(659, $order->shipping_pence);
        $this->assertSame(12659, $order->total_pence);
    }

    public function test_an_unknown_service_is_rejected_rather_than_shipped_free(): void
    {
        $this->addToCart($this->product(2000));

        $this->post(route('checkout.store'), $this->checkoutPayload([
            'shipping_method' => 'free-please',
        ]))->assertSessionHasErrors('shipping_method');

        $this->assertSame(0, Order::count());
    }

    public function test_a_missing_service_is_rejected(): void
    {
        $this->addToCart($this->product(2000));

        $payload = $this->checkoutPayload();
        unset($payload['shipping_method']);

        $this->post(route('checkout.store'), $payload)->assertSessionHasErrors('shipping_method');
        $this->assertSame(0, Order::count());
    }

    public function test_non_uk_addresses_are_turned_away_with_an_explanation(): void
    {
        // We ship worldwide, but only by email quote until real international
        // rates exist. Royal Mail domestic prices would badly undercharge.
        $this->addToCart($this->product(2000));

        $this->post(route('checkout.store'), $this->checkoutPayload([
            'country' => 'US',
        ]))->assertSessionHasErrors('country');

        $this->assertSame(0, Order::count());
    }

    /*
    |--------------------------------------------------------------------------
    | Collection in person
    |--------------------------------------------------------------------------
    */

    public function test_collection_can_be_ordered_without_a_delivery_address(): void
    {
        // The whole point of collection: nothing is posted, so nothing should
        // be demanded about where to post it.
        $this->addToCart($this->product(2000));

        $payload = $this->checkoutPayload(['shipping_method' => 'pickup']);
        unset($payload['line1'], $payload['city'], $payload['postcode'], $payload['country']);

        $this->post(route('checkout.store'), $payload)->assertSessionHasNoErrors();

        $order = Order::latest('id')->firstOrFail();

        $this->assertSame('pickup', $order->shipping_method);
        $this->assertTrue($order->isPickup());
        $this->assertSame(0, $order->shipping_pence);
        $this->assertSame(2000, $order->total_pence);
    }

    public function test_a_posted_order_still_requires_an_address(): void
    {
        // The address rule is conditional on collection, not switched off. A
        // parcel with nowhere to go must not be orderable.
        $this->addToCart($this->product(2000));

        $payload = $this->checkoutPayload();
        unset($payload['line1'], $payload['city'], $payload['postcode']);

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors(['line1', 'city', 'postcode']);

        $this->assertSame(0, Order::count());
    }

    public function test_a_collection_order_records_the_shop_address_not_a_blank_one(): void
    {
        // Every order view and both emails read the shipping address. A blank
        // one reads as an order going nowhere rather than one being collected.
        $this->addToCart($this->product(2000));

        $this->post(route('checkout.store'), $this->checkoutPayload([
            'shipping_method' => 'pickup',
        ]))->assertSessionHasNoErrors();

        $order = Order::latest('id')->firstOrFail();

        $this->assertStringContainsString(Delivery::pickupAddress(), $order->shipping_address['line1']);
        $this->assertSame('Test Buyer', $order->shipping_address['full_name']);

        // No stray comma-only line from the empty city and postcode.
        foreach ($order->shippingAddressLines() as $line) {
            $this->assertNotSame('', trim($line, " ,"), 'Blank or comma-only address line.');
        }
    }

    public function test_collection_is_free_even_below_the_free_delivery_threshold(): void
    {
        // Collection is not the standard service, so it does not go free by way
        // of the threshold — it has to be free on its own.
        $this->assertSame(0, Delivery::shippingPenceFor(100, 'pickup'));
        $this->assertSame(0, Delivery::shippingPenceFor(999999, 'pickup'));
    }
}
