<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        abort_unless($product->status === 'published', 404);

        $product->load(['images', 'variants', 'category', 'brand', 'approvedReviews.user']);

        $related = Product::published()
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->with('images', 'brand', 'approvedReviews')
            ->take(4)
            ->get();

        $moreFromBrand = collect();
        if ($product->brand_id) {
            $moreFromBrand = Product::published()
                ->where('id', '!=', $product->id)
                ->where('brand_id', $product->brand_id)
                ->with('images', 'category', 'approvedReviews')
                ->inRandomOrder()
                ->take(4)
                ->get();
        }

        return view('shop.show', compact('product', 'related', 'moreFromBrand'));
    }
}
