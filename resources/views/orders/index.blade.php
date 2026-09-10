<x-app-layout>
    <x-slot name="title">Order History — RetailerPOS</x-slot>

    <div class="space-y-6">

        <!-- Header & Search Filter Bar -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">Order History</h1>
                <p class="text-xs text-slate-500 font-medium">Browse and search past customer orders</p>
            </div>

            <!-- Email Search Form -->
            <form action="{{ route('orders.index') }}" method="GET" class="flex gap-2">
                <input type="text" 
                       name="email" 
                       value="{{ request('email') }}" 
                       placeholder="Search by customer email..." 
                       class="px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm w-64 md:w-80">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-sm transition-colors">
                    Search
                </button>
                @unless(empty(request('email')))
                    <a href="{{ route('orders.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-sm transition-colors flex items-center">
                        Clear
                    </a>
                @endunless
            </form>
        </div>

        <!-- Orders Data Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase font-bold text-slate-500 bg-slate-50">
                            <th class="py-3 px-4">Order ID</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-3 text-center">Items</th>
                            <th class="py-3 px-4 text-right">Grand Total</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Date</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-900">#{{ $order->id }}</td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-800">{{ $order->customer?->name ?? 'Unknown Customer' }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $order->customer?->email }}</div>
                                </td>
                                <td class="py-4 px-3 text-center font-semibold text-slate-600">
                                    {{ $order->items->count() }}
                                </td>
                                <td class="py-4 px-4 text-right font-black text-indigo-600 text-base">
                                    ${{ number_format($order->grand_total, 2) }}
                                </td>
                                <td class="py-4 px-3 text-center">
                                    <span @class([
                                        'inline-block px-2.5 py-0.5 text-xs font-black uppercase tracking-wider rounded-full border',
                                        $order->status->badgeClass(),
                                    ])>
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center text-xs text-slate-500">
                                    {{ $order->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <a href="{{ route('orders.show', $order->id) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg text-xs transition-colors inline-flex items-center gap-1">
                                        View Bill &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 font-medium italic">
                                    No orders found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @unless(!$orders->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $orders->links() }}
                </div>
            @endunless
        </div>

    </div>
</x-app-layout>
