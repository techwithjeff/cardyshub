<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'size',
        'concern',
        'skin_type',
        'key_active',
        'short_description',
        'description',
        'how_to_use',
        'ingredients_inci',
        'price_pence',
        'compare_at_price_pence',
        'sku',
        'stock_quantity',
        'status',
        'is_featured',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'price_pence' => 'integer',
            'compare_at_price_pence' => 'integer',
            'stock_quantity' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Only products that have at least one photo.
     *
     * For the shop grid a photoless product is fine — the card falls back to the
     * brand mark. On the homepage rails it is not: those are the shop window,
     * and a placeholder there reads as a broken site.
     */
    public function scopeWithImages(Builder $query): Builder
    {
        return $query->whereHas('images');
    }

    /**
     * The distinct concerns customers can filter by, with a product count each,
     * most-stocked first.
     *
     * `concern` is a free-text field in the admin, so the raw column holds the
     * same concern spelled several ways — "Dark spots", "Dark spots " and
     * "Dark Spots" are three rows but one thing to a shopper. Grouping on a
     * trimmed, lowercased key folds those together, and the label shown is
     * whichever spelling is used on the most products.
     *
     * Grouped in PHP rather than with GROUP BY on purpose: this feeds the
     * homepage, and a GROUP BY there is a temp table and a sort. See the note
     * in HomeController.
     *
     * Anything matched here must be matched the same way when filtering, or a
     * folded chip silently drops the products spelled differently. That pairing
     * lives in ShopController::applyConcern().
     *
     * @return Collection<int, array{label: string, count: int}>
     */
    public static function concernCounts(): Collection
    {
        return static::published()
            ->whereNotNull('concern')
            ->pluck('concern')
            ->map(fn ($concern) => trim((string) $concern))
            ->filter()
            ->groupBy(fn (string $concern) => mb_strtolower($concern))
            ->map(fn (Collection $spellings) => [
                // countBy()->sortDesc() puts the most-used spelling first; sort()
                // before it keeps the pick stable when two spellings tie.
                'label' => $spellings->sort()->countBy()->sortDesc()->keys()->first(),
                'count' => $spellings->count(),
            ])
            ->sortBy([['count', 'desc'], ['label', 'asc']])
            ->values();
    }

    /**
     * Newest first, with a stable tiebreaker.
     *
     * `latest()` alone is not deterministic here: the catalogue was seeded in
     * one go, so 32 products share a created_at to the second. MySQL is then
     * free to return any of them, and it genuinely does differ between machines
     * — the same code picked photographed products locally and photoless ones
     * on the server. Ordering by id as well makes the result reproducible.
     */
    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function getPriceAttribute(): float
    {
        return $this->price_pence / 100;
    }

    public function getCompareAtPriceAttribute(): ?float
    {
        return $this->compare_at_price_pence ? $this->compare_at_price_pence / 100 : null;
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }
}
