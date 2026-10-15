<?php

namespace App\Http\Controllers;

use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount(['products' => fn ($q) => $q->published()])->orderBy('name')->get();

        return view('brands.index', compact('brands'));
    }

    public function show(Brand $brand)
    {
        $products = $brand->products()->published()->with(['images', 'category', 'approvedReviews'])->paginate(12);

        // A-Z neighbours, so the range stays walkable from any brand page.
        $previous = Brand::where('name', '<', $brand->name)->orderByDesc('name')->first();
        $next = Brand::where('name', '>', $brand->name)->orderBy('name')->first();

        return view('brands.show', compact('brand', 'products', 'previous', 'next'));
    }
}
