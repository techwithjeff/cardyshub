<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;

class HomeController extends Controller
{
    /** How many concern chips the homepage shows before deferring to the shop page. */
    public const CONCERN_LIMIT = 8;

    /** Flat Lucide icon per category slug, used on the homepage category grid. */
    public const CATEGORY_ICONS = [
        'skincare' => 'sparkles',
        'serums' => 'droplet',
        'facial-toners' => 'spray-can',
        'body-lotions' => 'droplets',
        'bath-body' => 'bath',
    ];

    /*
    |--------------------------------------------------------------------------
    | Why this page is written the way it is
    |--------------------------------------------------------------------------
    |
    | Every query below deliberately avoids a server-side sort and an internal
    | temp table. `products` carries three TEXT columns (description,
    | how_to_use, ingredients_inci) and `brands` and `categories` carry one
    | each, and MySQL cannot hold a temp table containing TEXT in memory — it
    | is forced onto disk. On shared hosting that means an ordinary ORDER BY
    | on the shop window depends on free space in the server's MySQL tmpdir,
    | which is not something this application controls. When that partition
    | filled up, the homepage 500'd while every other page kept working.
    |
    | So: read down an index, and do the last mile in PHP. The sets involved
    | are dozens of rows, not thousands, and the ordering is identical.
    |
    */
    public function index()
    {
        // Resolved up front as a plain list of ids. whereHas('images') compiled
        // to an EXISTS subquery that MySQL materialised into a temp table; a
        // literal whereIn() reads straight down the index instead. Both rails
        // are limited to photographed products on purpose — a card falling back
        // to the brand mark looks broken in the shop window.
        $photographed = ProductImage::query()->pluck('product_id')->unique()->all();

        $cardData = ['images', 'category', 'brand', 'approvedReviews'];

        // newestFirst() rather than latest(): most of the catalogue shares one
        // seeded created_at, so created_at on its own is not deterministic
        // across machines. See the scope comment on the model.
        $featured = Product::published()->featured()
            ->whereIn('id', $photographed)
            ->with($cardData)->newestFirst()->take(4)->get();

        // The exclusion is folded into the same id list rather than added as a
        // separate whereNotIn: one IN list keeps the plan a single index range,
        // where the extra NOT IN gave the optimiser a reason to fall back to a
        // primary-key scan and sort the result.
        //
        // Excluded at all because otherwise the same card shows up twice on one
        // screen — once both rails are limited to photographed products the two
        // pools overlap heavily, and a featured product is usually a recent one.
        $newIn = Product::published()
            ->whereIn('id', array_values(array_diff($photographed, $featured->modelKeys())))
            ->with($cardData)->newestFirst()->take(4)->get();

        // Only categories with something in them, same as the brand rail below —
        // a tile reading "0 products" is a dead end in the shop window.
        //
        // The count is published-only. Counting every row meant a category could
        // advertise stock that nobody can actually see, and it also decided which
        // tiles were treated as empty.
        $categories = Category::withCount(['products' => fn ($q) => $q->published()])
            ->get()
            ->filter(fn (Category $category) => $category->products_count > 0)
            ->sortBy('sort_order')
            ->values();

        // Only brands with something to sell, deepest catalogue first — the rail
        // should never send someone to an empty brand page.
        //
        // Both the filter and the ordering are done in PHP. Ordering by the
        // withCount() alias is a guaranteed filesort, and whereHas() is another
        // materialised subquery; against 79 brands neither is worth a temp file.
        $brands = Brand::withCount(['products' => fn ($q) => $q->published()])
            ->get()
            ->filter(fn (Brand $brand) => $brand->products_count > 0)
            ->sortBy([['products_count', 'desc'], ['name', 'asc']])
            ->take(12)
            ->values();

        $brandTotal = Brand::count();

        // Capped, because `concern` is free text in the admin and the list grew
        // to 59 chips — a section taller than the phone screen, where most chips
        // led to a single product. The busiest few are the useful ones; the rest
        // are a click away on the shop page, which lists them all.
        //
        // concernCounts() also folds spellings that differ only by case or
        // stray whitespace, so the same label cannot appear twice.
        $allConcerns = Product::concernCounts();
        $concerns = $allConcerns->take(self::CONCERN_LIMIT);
        $concernTotal = $allConcerns->count();

        $heroHeading = Setting::get('homepage_hero_heading', "Skincare that actually fits your skin");
        $heroSubheading = Setting::get('homepage_hero_subheading', "Built by someone who spent years on trial and error, so you do not have to.");

        return view('home', compact('featured', 'newIn', 'categories', 'brands', 'brandTotal', 'concerns', 'concernTotal', 'heroHeading', 'heroSubheading'));
    }
}
