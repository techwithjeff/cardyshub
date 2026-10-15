<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()->wishlistedProducts()->with('images', 'category')->latest('wishlist_items.created_at')->get();

        return view('account.wishlist', compact('products'));
    }

    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();

        if ($user->wishlistedProducts()->where('product_id', $product->id)->exists()) {
            $user->wishlistedProducts()->detach($product->id);
            $wishlisted = false;
            $message = 'Removed from your wishlist.';
        } else {
            $user->wishlistedProducts()->syncWithoutDetaching([$product->id]);
            $wishlisted = true;
            $message = 'Saved to your wishlist.';
        }

        // JSON for the fetch-driven hearts, a redirect when JavaScript is off
        // or has not booted yet — the buttons stay real forms either way.
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'wishlisted' => $wishlisted,
                'product_id' => $product->id,
                'count' => $user->wishlistedProducts()->count(),
            ]);
        }

        return back()->with('success', $message);
    }
}
