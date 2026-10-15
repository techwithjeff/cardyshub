<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->get();

        return view('account.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $address = $request->user()->addresses()->create($validated);

        if ($address->is_default) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        return back()->with('success', 'Address saved.');
    }

    public function update(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $validated = $this->validated($request);

        $address->update($validated);

        if ($address->is_default) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        return back()->with('success', 'Address updated.');
    }

    public function destroy(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $address->delete();

        return back()->with('success', 'Address removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'full_name' => ['required', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postcode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }
}
