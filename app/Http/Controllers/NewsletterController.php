<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'consent' => ['accepted'],
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['email' => $validated['email']],
            ['consented_at' => now()]
        );

        return back()->with('success', 'You are in. Your 10% code is on its way to your inbox.');
    }
}
