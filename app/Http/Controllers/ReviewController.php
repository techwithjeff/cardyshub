<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'body' => $validated['body'],
                'is_approved' => false,
            ]
        );

        return back()->with('success', 'Thanks for your review — it will appear once approved.');
    }
}
