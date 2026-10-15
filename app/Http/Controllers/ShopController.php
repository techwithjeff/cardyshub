<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::published()->with(['images', 'category', 'brand', 'approvedReviews']);

        $activeCategory = null;
        if ($request->filled('category')) {
            $activeCategory = Category::where('slug', $request->string('category'))->first();
            $query->when($activeCategory, fn ($q) => $q->where('category_id', $activeCategory->id));
        }

        $activeBrand = null;
        if ($request->filled('brand')) {
            $activeBrand = Brand::where('slug', $request->string('brand'))->first();
            $query->when($activeBrand, fn ($q) => $q->where('brand_id', $activeBrand->id));
        }

        if ($request->filled('concern')) {
            $this->applyConcern($query, (string) $request->string('concern'));
        }

        if ($request->filled('skin_type')) {
            $query->where('skin_type', $request->string('skin_type'));
        }

        if ($request->filled('min_price')) {
            $query->where('price_pence', '>=', (float) $request->input('min_price') * 100);
        }

        if ($request->filled('max_price')) {
            $query->where('price_pence', '<=', (float) $request->input('max_price') * 100);
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('name', 'like', "%{$search}%");
        }

        match ($request->get('sort')) {
            'price_asc' => $query->orderBy('price_pence'),
            'price_desc' => $query->orderByDesc('price_pence'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        $brands = Brand::withCount('products')->orderBy('name')->get();
        // Alphabetical here rather than by popularity: this is a filter list to
        // scan for a known word, not a shop window. The homepage shows the same
        // folded set, ranked by product count and capped.
        $concerns = Product::concernCounts()->sortBy('label')->values();
        $skinTypes = Product::published()->whereNotNull('skin_type')->distinct()->orderBy('skin_type')->pluck('skin_type');

        return view('shop.index', compact('products', 'categories', 'brands', 'concerns', 'skinTypes', 'activeCategory', 'activeBrand'));
    }

    /**
     * Filter by concern the same way Product::concernCounts() groups them.
     *
     * The two have to agree. `concern` is free text, so "Dark spots",
     * "Dark spots " and "Dark Spots" are one chip in the list — and an exact
     * `where` would return only the products spelled the way the chip happens
     * to be labelled, silently hiding the rest.
     *
     * LOWER(TRIM()) rather than a LIKE: this matches the whole value, so
     * "Dry skin" never sweeps in "Extremely dry and rough skin". There is no
     * index on `concern` to lose, and the result set is paginated anyway.
     */
    private function applyConcern(Builder $query, string $concern): void
    {
        $query->whereRaw('LOWER(TRIM(concern)) = ?', [mb_strtolower(trim($concern))]);
    }
}
