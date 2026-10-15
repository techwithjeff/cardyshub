<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'discount_id',
        'cart_id',
        'email',
        'phone',
        'status',
        'currency',
        'subtotal_pence',
        'discount_pence',
        'shipping_pence',
        'shipping_method',
        'shipping_method_name',
        'total_pence',
        'shipping_address',
        'billing_address',
        'stripe_payment_intent_id',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * The shipping address as printable lines, empty parts dropped.
     *
     * Collection orders have no city, postcode or second line — there is
     * nothing to post. Templates used to concatenate those fields directly,
     * which on a collection order printed a line containing just a comma.
     *
     * @return array<int, string>
     */
    public function shippingAddressLines(): array
    {
        $address = $this->shipping_address ?? [];

        $cityAndPostcode = array_filter([
            trim((string) ($address['city'] ?? '')),
            trim((string) ($address['postcode'] ?? '')),
        ]);

        return array_values(array_filter([
            trim((string) ($address['full_name'] ?? '')),
            trim((string) ($address['line1'] ?? '')),
            trim((string) ($address['line2'] ?? '')),
            implode(', ', $cityAndPostcode),
            trim((string) ($address['country'] ?? '')),
        ], fn (string $line) => $line !== ''));
    }

    /** True when this order is being collected rather than posted. */
    public function isPickup(): bool
    {
        return $this->shipping_method === 'pickup';
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (! $order->order_number) {
                $order->order_number = 'CH-'.strtoupper(Str::random(8));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
