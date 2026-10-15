<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Orders</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @forelse($orders as $order)
                    <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0">
                        <div>
                            <a href="{{ route('account.orders.show', $order) }}" class="font-medium text-gray-900 hover:text-pink-deep">#{{ $order->order_number }}</a>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="text-xs font-medium uppercase px-2 py-1 rounded-full bg-pink-wash text-pink-deep">{{ $order->status }}</span>
                        <span class="font-medium text-gray-900">{{ money($order->total_pence) }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">You haven't placed any orders yet. <a href="{{ route('shop.index') }}" class="text-pink-deep hover:underline">Start shopping</a>.</p>
                @endforelse
            </div>
            <div class="mt-6">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
