<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'price_pence',
        'stock_quantity',
        'sku',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectivePricePenceAttribute(): int
    {
        return $this->price_pence ?? $this->product->price_pence;
    }
}
