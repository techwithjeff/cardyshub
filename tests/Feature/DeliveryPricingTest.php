<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('delivery_free_threshold', '100');
        Setting::set('delivery_rate_1st_class', '5.09');
        Setting::set('delivery_rate_2nd_class', '3.99');
        Setting::set('delivery_rate_signed_1st', '6.59');
        Setting::set('delivery_rate_signed_2nd', '5.49');
        Setting::set('delivery_rate_guaranteed_9am', '46.63');
        Setting::set('delivery_rate_guaranteed_9am_sat', '49.13');
        Setting::set('delivery_rate_guaranteed_1pm', '10.25');
        Setting::set('delivery_rate_guaranteed_1pm_sat', '12.75');
        Setting::set('delivery_rate_tracked_24', '4.50');
        Setting::set('delivery_rate_tracked_24_sig', '5.75');
        Setting::set('delivery_rate_tracked_48', '3.54');
        Setting::set('delivery_rate_tracked_48_sig', '4.79');
        Setting::set('delivery_cutoff_time', '5pm');
    }

    public function test_values_format_correctly(): void
    {
        $this->assertSame('£100', Delivery::freeThreshold());
        $this->assertSame('£5.09', Delivery::standardRate());
        $this->assertSame('5pm', Delivery::cutOffTime());
        $this->assertSame(10000, Delivery::freeThresholdPence());
    }

    public function test_the_full_royal_mail_card_is_offered_in_order(): void
    {
        // The client supplied this rate sheet and asked for it exactly: same
        // twelve services, same order, same prices.
        $expected = [
            ['1st-class', '1st Class Service', 509],
            ['2nd-class', '2nd Class Service', 399],
            ['signed-1st', 'Signed For 1st Class', 659],
            ['signed-2nd', 'Signed For 2nd Class', 549],
            ['guaranteed-9am', 'Guaranteed by 9am', 4663],
            ['guaranteed-9am-sat', 'Guaranteed by 9am with Saturday', 4913],
            ['guaranteed-1pm', 'Guaranteed by 1pm', 1025],
            ['guaranteed-1pm-sat', 'Guaranteed by 1pm with Saturday', 1275],
            ['tracked-24', 'Tracked 24', 450],
            ['tracked-24-sig', 'Tracked 24 With Signature', 575],
            ['tracked-48', 'Tracked 48', 354],
            ['tracked-48-sig', 'Tracked 48 With Signature', 479],
        ];

        // postedMethods(), so this stays a test of the rate sheet the client
        // supplied. Collection is not a Royal Mail service and is covered by
        // its own tests below.
        $actual = array_map(
            fn ($m) => [$m->code, $m->name, $m->pricePence],
            Delivery::postedMethods()
        );

        $this->assertSame($expected, $actual);
    }

    public function test_the_standard_service_is_first_class(): void
    {
        // Matches the pre-selected option on the client's sheet, and 1st Class
        // aims for next working day so the default can keep the site's promise.
        $this->assertSame('1st-class', Delivery::standardMethod()->code);
    }

    public function test_only_the_standard_service_goes_free_over_the_threshold(): void
    {
        $standard = Delivery::standardMethod();

        $this->assertSame(509, $standard->pricePenceFor(9999));
        $this->assertSame(0, $standard->pricePenceFor(10000));

        // Everything else is charged in full however large the basket. Without
        // this a £100 order could ship Guaranteed by 9am at £46.63 of our money.
        foreach (Delivery::methods() as $method) {
            if ($method->isStandard) {
                continue;
            }

            $this->assertSame(
                $method->pricePence,
                $method->pricePenceFor(50000),
                "[{$method->code}] went free on a large basket."
            );
        }
    }

    public function test_an_unknown_method_falls_back_to_standard_not_to_free(): void
    {
        // A tampered form field must never be a discount.
        $this->assertNull(Delivery::method('free-please'));
        $this->assertSame(509, Delivery::shippingPenceFor(9999, 'free-please'));
        $this->assertSame(509, Delivery::shippingPenceFor(9999, null));
    }

    public function test_cheapest_method_is_found_by_price_not_by_position(): void
    {
        $cheapest = Delivery::cheapestMethod();

        $this->assertSame('tracked-48', $cheapest->code);

        // Posted services only. Collection is free, so including it would make
        // "delivery from £x" copy read "from £0" and promise postage we do not
        // sell at that price.
        foreach (Delivery::postedMethods() as $method) {
            $this->assertGreaterThanOrEqual($cheapest->pricePence, $method->pricePence);
        }

        $this->assertFalse($cheapest->isPickup, 'Collection must never be quoted as the cheapest postage.');
    }

    public function test_every_method_has_a_distinct_code_and_a_price(): void
    {
        $methods = Delivery::methods();
        $codes = array_map(fn ($m) => $m->code, $methods);

        $this->assertSame($codes, array_unique($codes), 'Duplicate shipping method code.');
        $this->assertCount(1, array_filter($methods, fn ($m) => $m->isStandard), 'There must be exactly one standard method.');

        $this->assertCount(1, array_filter($methods, fn ($m) => $m->isPickup), 'There must be exactly one collection method.');

        foreach ($methods as $method) {
            $this->assertNotSame('', $method->arrival, "[{$method->code}] has no arrival estimate.");
        }

        // Every posted service must cost something — a £0 postage would be a
        // silent giveaway. Collection is the one deliberate exception.
        foreach (Delivery::postedMethods() as $method) {
            $this->assertGreaterThan(0, $method->pricePence, "[{$method->code}] has no price.");
        }

        $this->assertSame(0, Delivery::pickupMethod()->pricePence, 'Collection must be free.');
    }

    public function test_rates_come_from_settings(): void
    {
        Setting::set('delivery_rate_1st_class', '5.25');
        $this->assertSame('£5.25', Delivery::standardRate());
    }

    public function test_threshold_is_consistent_everywhere_it_is_shown(): void
    {
        foreach (['/', '/delivery', '/faqs', '/cart', '/wholesale'] as $path) {
            $html = $this->get($path)->getContent();
            $this->assertStringNotContainsString('over £40', $html, "stale £40 threshold on {$path}");
            $this->assertStringNotContainsString('£3.50', $html, "stale £3.50 rate on {$path}");
        }
    }

    public function test_delivery_page_lists_every_service_it_charges_for(): void
    {
        $response = $this->get('/delivery');

        foreach (Delivery::methods() as $method) {
            $response->assertSee($method->name)->assertSee($method->priceLabel());
        }

        $response->assertSee('5pm')->assertSee('£100');
    }
}
