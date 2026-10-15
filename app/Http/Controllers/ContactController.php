<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use App\Models\Setting;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'order_number' => ['nullable', 'string', 'max:64'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactSubmission::create($validated);

        $email = Setting::get('contact_email', 'hello@cardyshub.com');

        return back()->with('success', "Thanks, your message is with us. You will get a reply from {$email} within one working day. Check your spam folder if it has not landed by then.");
    }
}
