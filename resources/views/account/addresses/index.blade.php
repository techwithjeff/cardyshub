<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Addresses</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if($addresses->isNotEmpty())
                <div class="bg-white shadow sm:rounded-lg divide-y divide-gray-100">
                    @foreach($addresses as $address)
                        <div x-data="{ editing: false }" class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="text-sm text-gray-700">
                                    @if($address->is_default)
                                        <span class="inline-block mb-1 text-xs font-medium uppercase px-2 py-0.5 rounded-full bg-pink-wash text-pink-deep">Default</span>
                                    @endif
                                    <p class="font-medium text-gray-900">{{ $address->full_name }}</p>
                                    <p>{{ $address->line1 }}</p>
                                    @if($address->line2)<p>{{ $address->line2 }}</p>@endif
                                    <p>{{ $address->city }}, {{ $address->postcode }}</p>
                                    <p>{{ $address->country }}</p>
                                    @if($address->phone)<p>{{ $address->phone }}</p>@endif
                                </div>
                                <div class="flex flex-col items-end gap-2 text-sm">
                                    <button type="button" @click="editing = !editing" class="text-pink-deep hover:underline">Edit</button>
                                    <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Remove this address?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-500">Delete</button>
                                    </form>
                                </div>
                            </div>

                            <form x-show="editing" x-cloak method="POST" action="{{ route('account.addresses.update', $address) }}" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @csrf @method('PATCH')
                                <input type="text" name="full_name" value="{{ $address->full_name }}" placeholder="Full name" required class="sm:col-span-2 rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                                <input type="text" name="line1" value="{{ $address->line1 }}" placeholder="Address line 1" required class="sm:col-span-2 rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                                <input type="text" name="line2" value="{{ $address->line2 }}" placeholder="Address line 2" class="sm:col-span-2 rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                                <input type="text" name="city" value="{{ $address->city }}" placeholder="City" required class="rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                                <input type="text" name="postcode" value="{{ $address->postcode }}" placeholder="Postcode" required class="rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                                <input type="text" name="country" value="{{ $address->country }}" placeholder="Country code (e.g. GB)" required maxlength="2" class="rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                                <input type="text" name="phone" value="{{ $address->phone }}" placeholder="Phone" class="rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                                <label class="sm:col-span-2 flex items-center gap-2 text-sm text-gray-600">
                                    <input type="checkbox" name="is_default" value="1" @checked($address->is_default) class="rounded border-gray-300 text-pink focus:ring-pink">
                                    Set as default
                                </label>
                                <button type="submit" class="sm:col-span-2 rounded-full bg-pink px-6 py-2 text-white text-sm font-semibold hover:bg-pink-deep transition">Save changes</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add a new address</h3>
                <form method="POST" action="{{ route('account.addresses.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @csrf
                    <input type="text" name="full_name" placeholder="Full name" required class="sm:col-span-2 rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                    <input type="text" name="line1" placeholder="Address line 1" required class="sm:col-span-2 rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                    <input type="text" name="line2" placeholder="Address line 2" class="sm:col-span-2 rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                    <input type="text" name="city" placeholder="City" required class="rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                    <input type="text" name="postcode" placeholder="Postcode" required class="rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                    <input type="text" name="country" placeholder="Country code (e.g. GB)" value="GB" required maxlength="2" class="rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                    <input type="text" name="phone" placeholder="Phone" class="rounded-lg border-gray-300 text-sm focus:border-pink focus:ring-pink">
                    <label class="sm:col-span-2 flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-pink focus:ring-pink">
                        Set as default
                    </label>
                    <button type="submit" class="sm:col-span-2 rounded-full bg-pink px-6 py-2 text-white text-sm font-semibold hover:bg-pink-deep transition">Add address</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
