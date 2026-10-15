<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\WholesaleEnquiry;
use Illuminate\Http\Request;

class WholesaleController extends Controller
{
    /** Kept in one place so the form and the validator cannot drift apart. */
    public const BUSINESS_TYPES = [
        'Retail store',
        'Online retailer',
        'Salon',
        'Spa',
        'Subscription box',
        'Corporate gifting',
        'Distributor',
        'Other',
    ];

    public const ORDER_VALUES = [
        'Under £500',
        '£500 to £1,000',
        '£1,000 to £2,500',
        '£2,500 to £5,000',
        'Over £5,000',
        'Not sure yet',
    ];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'in:'.implode(',', self::BUSINESS_TYPES)],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'opening_order_value' => ['nullable', 'string', 'in:'.implode(',', self::ORDER_VALUES)],
            'message' => ['required', 'string', 'max:5000'],
            // Consent is recorded by the act of submitting; the checkbox must be ticked.
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Please confirm you have read the privacy policy.',
        ]);

        unset($validated['consent']);

        WholesaleEnquiry::create($validated);

        $email = trim((string) Setting::get('wholesale_email', ''));
        $fallback = $email !== '' ? $email : Setting::get('contact_email', 'hello@cardyshub.com');

        return back()->with('success', "Thank you. Your enquiry has reached us and we will be in touch within two working days. If your enquiry is urgent, email us directly at {$fallback}.");
    }
}
