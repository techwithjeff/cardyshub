<?php

namespace App\Support;

/**
 * One delivery option offered at checkout.
 *
 * Prices are Royal Mail's published rates for the services we actually use.
 * They live in settings rather than here so they can be corrected when Royal
 * Mail changes its rate card without a deploy — see Delivery::methods().
 */
final class ShippingMethod
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $arrival,
        public readonly int $pricePence,
        /** The default selection, and the one the free-delivery threshold applies to. */
        public readonly bool $isStandard = false,
        /**
         * Collection in person rather than a posted parcel.
         *
         * Drives three things a £0 price alone cannot: the checkout hides the
         * delivery address for it, the order stores the shop's address instead
         * of the customer's, and it is kept out of "delivery from £x" copy so a
         * free collection never advertises itself as the cheapest postage.
         */
        public readonly bool $isPickup = false,
    ) {}

    /** "£4.50", or "£4" for whole pounds. */
    public function price(): string
    {
        return Delivery::money($this->pricePence);
    }

    /** What to print in a price column: "Free" reads better than "£0". */
    public function priceLabel(): string
    {
        return $this->pricePence === 0 ? 'Free' : $this->price();
    }

    /**
     * What this costs on a given basket.
     *
     * Only the standard service goes free over the threshold. Everything else
     * is an upgrade and is charged in full, which is what stops a £100 order
     * from being posted by guaranteed next-day at our expense.
     */
    public function pricePenceFor(int $subtotalPence): int
    {
        if ($this->isStandard && $subtotalPence >= Delivery::freeThresholdPence()) {
            return 0;
        }

        return $this->pricePence;
    }

    public function isFreeOn(int $subtotalPence): bool
    {
        return $this->pricePenceFor($subtotalPence) === 0;
    }
}
