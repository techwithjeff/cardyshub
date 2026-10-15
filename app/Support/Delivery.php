<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Single source of truth for delivery pricing.
 *
 * Before this existed the numbers were duplicated in four places and had
 * already drifted: OrderService charged £3.50 and gave free delivery over £35,
 * the cart progress bar counted up to £40, and the marketing copy said £40.
 * Everything now reads from the same settings, so changing the threshold is a
 * one-line change that the checkout, the cart and the copy all follow.
 *
 * Settings hold plain pound amounts as strings ("100", "3", "14").
 */
class Delivery
{
    public static function freeThresholdPence(): int
    {
        return self::pence('delivery_free_threshold', 100);
    }

    /**
     * The delivery options offered at checkout.
     *
     * Royal Mail's full card, in the order and at the prices on the rate sheet
     * the client supplied. Order matters: it is what customers were shown, so
     * it is kept rather than sorted by price.
     *
     * 1st Class is the standard, matching the pre-selected option on that sheet.
     * It also aims for next working day, so the default can keep the site's
     * next-day promise. Everything else is an upgrade or a cheaper, slower
     * alternative, and none of them go free — see ShippingMethod.
     *
     * UK only. International orders are quoted by email — see the Delivery page.
     *
     * @return array<int, ShippingMethod>
     */
    public static function methods(): array
    {
        return [
            // Collection first: it is free, and someone local should see it
            // before scrolling a rate card of twelve postage options.
            //
            // Hardcoded at zero rather than read from settings — a collection
            // the customer travels for is not something to put a price on, and a
            // stray value in the settings table should not be able to add one.
            new ShippingMethod(
                code: 'pickup',
                name: 'Collect in person',
                arrival: 'Ready within 2 working days, '.self::pickupAddress(),
                pricePence: 0,
                isPickup: true,
            ),
            new ShippingMethod(
                code: '1st-class',
                name: '1st Class Service',
                arrival: 'Next working day, including Saturday',
                pricePence: self::pence('delivery_rate_1st_class', 5.09),
                isStandard: true,
            ),
            new ShippingMethod(
                code: '2nd-class',
                name: '2nd Class Service',
                arrival: '2 to 3 working days',
                pricePence: self::pence('delivery_rate_2nd_class', 3.99),
            ),
            new ShippingMethod(
                code: 'signed-1st',
                name: 'Signed For 1st Class',
                arrival: 'Next working day, signature on delivery',
                pricePence: self::pence('delivery_rate_signed_1st', 6.59),
            ),
            new ShippingMethod(
                code: 'signed-2nd',
                name: 'Signed For 2nd Class',
                arrival: '2 to 3 working days, signature on delivery',
                pricePence: self::pence('delivery_rate_signed_2nd', 5.49),
            ),
            new ShippingMethod(
                code: 'guaranteed-9am',
                name: 'Guaranteed by 9am',
                arrival: 'Next working day before 9am, guaranteed',
                pricePence: self::pence('delivery_rate_guaranteed_9am', 46.63),
            ),
            new ShippingMethod(
                code: 'guaranteed-9am-sat',
                name: 'Guaranteed by 9am with Saturday',
                arrival: 'Saturday before 9am, guaranteed',
                pricePence: self::pence('delivery_rate_guaranteed_9am_sat', 49.13),
            ),
            new ShippingMethod(
                code: 'guaranteed-1pm',
                name: 'Guaranteed by 1pm',
                arrival: 'Next working day before 1pm, guaranteed',
                pricePence: self::pence('delivery_rate_guaranteed_1pm', 10.25),
            ),
            new ShippingMethod(
                code: 'guaranteed-1pm-sat',
                name: 'Guaranteed by 1pm with Saturday',
                arrival: 'Saturday before 1pm, guaranteed',
                pricePence: self::pence('delivery_rate_guaranteed_1pm_sat', 12.75),
            ),
            new ShippingMethod(
                code: 'tracked-24',
                name: 'Tracked 24',
                arrival: 'Next working day, tracked',
                pricePence: self::pence('delivery_rate_tracked_24', 4.50),
            ),
            new ShippingMethod(
                code: 'tracked-24-sig',
                name: 'Tracked 24 With Signature',
                arrival: 'Next working day, tracked, signature on delivery',
                pricePence: self::pence('delivery_rate_tracked_24_sig', 5.75),
            ),
            new ShippingMethod(
                code: 'tracked-48',
                name: 'Tracked 48',
                arrival: '2 to 3 working days, tracked',
                pricePence: self::pence('delivery_rate_tracked_48', 3.54),
            ),
            new ShippingMethod(
                code: 'tracked-48-sig',
                name: 'Tracked 48 With Signature',
                arrival: '2 to 3 working days, tracked, signature on delivery',
                pricePence: self::pence('delivery_rate_tracked_48_sig', 4.79),
            ),
        ];
    }

    /**
     * Cheapest service on the card, for "delivery from £x" copy.
     *
     * Collection is excluded. It is free, so including it would turn every
     * "delivery from" line into "from £0" and promise postage we do not offer.
     */
    public static function cheapestMethod(): ShippingMethod
    {
        $methods = self::postedMethods();

        usort($methods, fn (ShippingMethod $a, ShippingMethod $b) => $a->pricePence <=> $b->pricePence);

        return $methods[0];
    }

    /**
     * Everything that actually goes in the post.
     *
     * For copy that counts or prices the courier services, where collection is
     * not one of them.
     *
     * @return array<int, ShippingMethod>
     */
    public static function postedMethods(): array
    {
        return array_values(array_filter(self::methods(), fn (ShippingMethod $m) => ! $m->isPickup));
    }

    public static function pickupMethod(): ?ShippingMethod
    {
        foreach (self::methods() as $method) {
            if ($method->isPickup) {
                return $method;
            }
        }

        return null;
    }

    /** Where collection orders are picked up from. */
    public static function pickupAddress(): string
    {
        return trim((string) Setting::get('pickup_address', '')) ?: 'CV1, Coventry, UK';
    }

    public static function method(?string $code): ?ShippingMethod
    {
        foreach (self::methods() as $method) {
            if ($method->code === $code) {
                return $method;
            }
        }

        return null;
    }

    /** The default selection, and the only one the free threshold applies to. */
    public static function standardMethod(): ShippingMethod
    {
        foreach (self::methods() as $method) {
            if ($method->isStandard) {
                return $method;
            }
        }

        return self::methods()[0];
    }

    public static function standardRatePence(): int
    {
        return self::standardMethod()->pricePence;
    }

    /**
     * Shipping charged on a given subtotal for a given service.
     *
     * An unknown or missing code falls back to the standard service rather
     * than to free: a tampered form field should never be a discount.
     */
    public static function shippingPenceFor(int $subtotalPence, ?string $methodCode = null): int
    {
        $method = self::method($methodCode) ?? self::standardMethod();

        return $method->pricePenceFor($subtotalPence);
    }

    public static function cutOffTime(): string
    {
        return trim((string) Setting::get('delivery_cutoff_time', '')) ?: '5pm';
    }

    /** "£3" for whole pounds, "£3.50" otherwise. */
    public static function money(int $pence): string
    {
        return $pence % 100 === 0
            ? '£'.number_format($pence / 100)
            : '£'.number_format($pence / 100, 2);
    }

    public static function freeThreshold(): string
    {
        return self::money(self::freeThresholdPence());
    }

    public static function standardRate(): string
    {
        return self::money(self::standardRatePence());
    }


    protected static function pence(string $key, float $fallback): int
    {
        $raw = trim((string) Setting::get($key, ''));
        $pounds = $raw === '' ? $fallback : (float) preg_replace('/[^0-9.]/', '', $raw);

        return (int) round($pounds * 100);
    }
}
