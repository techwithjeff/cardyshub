<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Support\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The collection address, and what adding it does to data that already exists.
 *
 * This shipped to a live site with real orders in the table, so the tests that
 * matter most here are the ones proving nothing already stored changes shape.
 */
class PickupAddressSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_address_works_before_it_has_ever_been_saved(): void
    {
        // The live settings table has no pickup_address row. Nothing needs to
        // be inserted for collection to work — the default stands in.
        $this->assertSame(0, Setting::where('key', 'pickup_address')->count());
        $this->assertSame('CV1, Coventry, UK', Delivery::pickupAddress());
    }

    public function test_saving_an_address_replaces_the_default_everywhere(): void
    {
        Setting::set('pickup_address', '42 New Street, Coventry CV1 2AB');

        $this->assertSame('42 New Street, Coventry CV1 2AB', Delivery::pickupAddress());

        // The three customer-facing places it appears.
        $this->get('/delivery')->assertSee('42 New Street, Coventry CV1 2AB');
        $this->get('/faqs')->assertSee('42 New Street, Coventry CV1 2AB', false);
        $this->assertStringContainsString(
            '42 New Street, Coventry CV1 2AB',
            Delivery::pickupMethod()->arrival
        );
    }

    public function test_clearing_the_field_restores_the_default_rather_than_showing_nothing(): void
    {
        // Saving the settings form writes every field, so a cleared box stores
        // an empty string. That must not leave "Collect from" followed by a
        // blank — the same trap that blanked the founder photo.
        Setting::set('pickup_address', '');
        $this->assertSame('CV1, Coventry, UK', Delivery::pickupAddress());

        Setting::set('pickup_address', '   ');
        $this->assertSame('CV1, Coventry, UK', Delivery::pickupAddress());
    }

    public function test_orders_placed_before_this_change_are_untouched(): void
    {
        // An order exactly as the live table already holds them: a full posted
        // address and a Royal Mail service. Nothing about it should read
        // differently now that collection exists.
        $order = Order::create([
            'order_number' => 'CH-LEGACY01',
            'email' => 'buyer@example.com',
            'status' => 'paid',
            'subtotal_pence' => 2000,
            'shipping_pence' => 509,
            'discount_pence' => 0,
            'total_pence' => 2509,
            'shipping_method' => '1st-class',
            'shipping_method_name' => '1st Class Service',
            'shipping_address' => [
                'full_name' => 'Old Customer',
                'phone' => null,
                'line1' => '1 Test Street',
                'line2' => null,
                'city' => 'London',
                'postcode' => 'E1 6AN',
                'country' => 'GB',
            ],
        ]);

        $this->assertFalse($order->isPickup());

        // Identical to what the old templates concatenated by hand, with the
        // empty line2 dropped exactly as the old @if did.
        $this->assertSame([
            'Old Customer',
            '1 Test Street',
            'London, E1 6AN',
            'GB',
        ], $order->shippingAddressLines());
    }

    public function test_a_collection_order_never_renders_an_empty_line(): void
    {
        $order = Order::create([
            'order_number' => 'CH-PICKUP01',
            'email' => 'buyer@example.com',
            'status' => 'paid',
            'subtotal_pence' => 2000,
            'shipping_pence' => 0,
            'discount_pence' => 0,
            'total_pence' => 2000,
            'shipping_method' => 'pickup',
            'shipping_method_name' => 'Collect in person',
            'shipping_address' => [
                'full_name' => 'Local Customer',
                'phone' => null,
                'line1' => 'Collection — '.Delivery::pickupAddress(),
                'line2' => null,
                'city' => null,
                'postcode' => null,
                'country' => 'GB',
            ],
        ]);

        $this->assertTrue($order->isPickup());

        $this->assertSame([
            'Local Customer',
            'Collection — CV1, Coventry, UK',
            'GB',
        ], $order->shippingAddressLines());
    }
}
