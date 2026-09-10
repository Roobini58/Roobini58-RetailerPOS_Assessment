<x-app-layout>
    <x-slot name="title">Bill #{{ $order->id }} — RetailerPOS</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Top Navigation Action Bar -->
        <div class="flex items-center justify-between print:hidden">
            <a href="{{ route('orders.index') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Orders
            </a>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text text-xs font-bold uppercase tracking-wider rounded-lg flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Bill
                </button>
                <a href="{{ route('orders.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm">
                    + New Bill
                </a>
            </div>
        </div>

        <!-- Printable Invoice Tax Card -->
        <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-xl space-y-6 relative overflow-hidden print:p-0 print:border-none print:shadow-none print:rounded-none">

            <!-- Header Title -->
            <div class="flex justify-between items-start border-b border-slate-200 pb-6">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-1">Official Tax Receipt</div>
                    <h1 class="text-3xl font-extrabold text-slate-900">Invoice #{{ sprintf('%06d', $order->id) }}</h1>
                    <div class="text-xs text-slate-400 mt-1 font-mono">Date: {{ $order->created_at->format('M d, Y — H:i:s A') }}</div>
                </div>
                <div class="text-right">
                    <span @class([
                        'inline-block px-3 py-1 text-xs font-black uppercase tracking-wider rounded-full border',
                        $order->status->badgeClass(),
                    ])>
                        {{ $order->status->label() }}
                    </span>
                </div>
            </div>

            <!-- Customer & Store Metadata -->
            <div class="grid grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs">
                <div>
                    <div class="font-bold text-slate-400 uppercase tracking-wider mb-1">Billed To</div>
                    <div class="font-extrabold text-slate-900 text-sm">{{ $order->customer?->name ?? 'Guest Customer' }}</div>
                    <div class="text-slate-600 font-mono mt-0.5">{{ $order->customer?->email }}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-slate-400 uppercase tracking-wider mb-1">Merchant Info</div>
                    <div class="font-bold text-slate-800">RetailerPOS Store #001</div>
                    <div class="text-slate-500">123 Main Street, Retail City</div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">Item Description</th>
                        <th class="py-2.5 px-2 text-center">Unit Price</th>
                        <th class="py-2.5 px-2 text-center">Qty</th>
                        <th class="py-2.5 px-3 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($order->items as $item)
                        <tr>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-800">{{ $item->product?->name ?? 'Deleted Product' }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->product?->code }}</div>
                            </td>
                            <td class="py-3 px-2 text-center text-slate-600">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 px-2 text-center font-bold text-slate-800">{{ $item->quantity }}</td>
                            <td class="py-3 px-3 text-right font-extrabold text-slate-900">${{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400 italic">No line items in this invoice.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Summary Totals -->
            <div class="border-t border-slate-200 pt-4 max-w-xs ml-auto space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-800">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Tax Amount:</span>
                    <span class="font-bold text-slate-800">${{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-black text-slate-900 border-t border-slate-200 pt-2">
                    <span>Grand Total:</span>
                    <span class="text-indigo-600 text-xl">${{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>

            <!-- Footer Note -->
            @unless($order->status->isCancelled())
                <div class="border-t border-slate-100 mt-8 pt-4 text-center text-xs text-slate-400">
                    Thank you for shopping with us! For support or inquiries, email support@retailerpos.com.
                </div>
            @endunless

        </div>

    </div>
</x-app-layout>
