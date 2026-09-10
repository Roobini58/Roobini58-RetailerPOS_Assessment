<x-app-layout>
    <x-slot name="title">Store Billing — New Order</x-slot>

    @unless(!session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-100 border border-emerald-300 text-emerald-900 rounded-xl text-sm font-bold flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endunless

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Columns: Billing Form & Line Items -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Customer Details Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Customer Information
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Customer Email *</label>
                        <input type="email" 
                               id="customer_email" 
                               oninput="clearError('customer_email'); "
                               placeholder="customer@example.com" 
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                        <div id="error_customer_email"></div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Customer Name *</label>
                        <input type="text" 
                               id="customer_name" 
                               oninput="clearError('customer_name')"
                               placeholder="Full Name" 
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                        <div id="error_customer_name"></div>
                    </div>
                </div>
            </div>

            <!-- Products & Line Items Table Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    Order Items
                </h2>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-6">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Select Product</label>
                        <select id="selected_product_id" onchange="clearError('selected_product'); clearError('items');" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                            <option value="">-- Choose Product --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" 
                                        data-name="{{ $p->name }}" 
                                        data-code="{{ $p->code }}" 
                                        data-price="{{ $p->price_per_unit }}" 
                                        data-tax="{{ $p->tax_percentage }}" 
                                        data-stock="{{ $p->stock_on_hand }}"
                                        @disabled($p->stock_on_hand <= 0)>
                                    {{ $p->name }} ({{ $p->code }}) - ${{ number_format($p->price_per_unit, 2) }} [Stock: {{ $p->stock_on_hand }}]
                                </option>
                            @endforeach
                        </select>
                        <div id="error_items"></div>
                        <div id="error_selected_product"></div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Qty</label>
                        <input type="number" id="selected_qty" min="1" value="1" oninput="clearError('selected_qty')" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                        <div id="error_selected_qty"></div>
                    </div>

                    <div class="sm:col-span-3">
                        <button type="button" onclick="addItem()" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Product
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase font-bold text-slate-500 bg-slate-50">
                                <th class="py-3 px-3">Product</th>
                                <th class="py-3 px-2 text-center">Unit Price</th>
                                <th class="py-3 px-2 text-center">Tax %</th>
                                <th class="py-3 px-2 text-center">Qty</th>
                                <th class="py-3 px-3 text-right">Line Total</th>
                                <th class="py-3 px-2 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="items_table_body" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 font-medium italic">
                                    No items added yet. Choose a product above to build the order.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column: Low Stock Panel & Payment Summary Card -->
        <div class="space-y-6">

            <!-- Low Stock Panel -->
            <div class="bg-amber-50 rounded-2xl p-5 border-2 border-amber-300 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-amber-900 text-sm flex items-center gap-1.5">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Low Stock Alert
                    </h3>
                    <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-amber-200 text-amber-900">&le; {{ config('inventory.low_stock_threshold') }} units</span>
                </div>

                <ul class="space-y-2 text-xs">
                    @forelse($lowStockProducts as $p)
                        <li @class([
                            'flex items-center justify-between p-2 rounded-lg border',
                            'bg-amber-100/70 border-amber-200' => $p->stock_on_hand > 2,
                            'bg-rose-100/80 border-rose-300 text-rose-950 font-bold' => $p->stock_on_hand <= 2,
                        ])>
                            <div>
                                <span class="font-bold text-amber-950">{{ $p->name }}</span>
                                <span class="text-[10px] text-amber-700 block font-mono">{{ $p->code }}</span>
                            </div>
                            <span @class([
                                'font-black px-2 py-0.5 rounded text-white',
                                'bg-amber-600' => $p->stock_on_hand > 2,
                                'bg-rose-600 animate-pulse' => $p->stock_on_hand <= 2,
                            ])>{{ $p->stock_on_hand }} left</span>
                        </li>
                    @empty
                        <li class="text-xs text-amber-700 italic">All products are adequately stocked.</li>
                    @endforelse
                </ul>

                @unless($lowStockProducts->isEmpty())
                    <div class="mt-3 text-[11px] text-amber-800 font-semibold bg-amber-100/50 p-2 rounded border border-amber-200">
                        ⚡ Tip: Restock low stock items soon to avoid transaction failures.
                    </div>
                @endunless
            </div>

            <!-- Payment Summary Card (Submits via $.ajax) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-4">
                <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Payment Summary
                </h2>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600"><span>Subtotal:</span><span id="subtotal_display" class="font-semibold text-slate-800">$0.00</span></div>
                    <div class="flex justify-between text-slate-600"><span>Tax Amount:</span><span id="tax_display" class="font-semibold text-slate-800">$0.00</span></div>
                    <div class="flex justify-between text-base font-extrabold text-slate-900 border-t border-slate-100 pt-2">
                        <span>Grand Total:</span><span id="grand_total_display" class="text-indigo-600 text-lg">$0.00</span>
                    </div>
                </div>

                <!-- Cash Return Calculator -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Amount Given by Customer</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="number" step="0.01" min="0" id="amount_given" oninput="calculateChange()" placeholder="0.00" class="w-full pl-7 pr-3 py-2 rounded-lg border border-slate-300 text-sm font-bold text-slate-900">
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-sm pt-1">
                        <span class="font-bold text-slate-700">Balance to Return:</span>
                        <span id="balance_to_return" class="font-black text-emerald-600 text-base">$0.00</span>
                    </div>

                    <div id="denomination_box" class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs hidden">
                        <span class="font-bold text-emerald-900 block mb-1">Denomination Breakdown:</span>
                        <span id="denomination_breakdown" class="font-mono text-emerald-700 font-semibold"></span>
                    </div>
                </div>

                <!-- General Error Message -->
                <div id="error_general"></div>

                <!-- Submit Button via jQuery $.ajax -->
                <button type="button" 
                        id="submit_btn"
                        onclick="submitOrderViaJQueryAjax()"
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-emerald-600 hover:from-indigo-700 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg transition-all text-base flex items-center justify-center gap-2">
                    <span id="btn_label" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Generate Bill & Complete Order
                    </span>
                    <span id="btn_spinner" class="hidden flex items-center gap-2">
                        <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Processing via ...
                    </span>
                </button>
            </div>

        </div>

    </div>

    <!-- Pure Vanilla JavaScript (onclick / oninput / DOM manipulation) + jQuery $.ajax -->
    <script>
        // Line items state array
        let items = [];
        let subtotal = 0;
        let taxAmount = 0;
        let grandTotal = 0;

        function showError(fieldKey, message) {
            const fieldId = fieldKey.replace('.', '_');
            const targetEl = document.getElementById('error_' + fieldId);
            if (targetEl) {
                targetEl.innerHTML = `
                    <p class="text-xs font-semibold text-rose-600 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>${message}</span>
                    </p>`;
            }
        }

        function clearError(fieldKey) {
            const fieldId = fieldKey.replace('.', '_');
            const targetEl = document.getElementById('error_' + fieldId);
            if (targetEl) {
                targetEl.innerHTML = '';
            }
        }

        

        function addItem() {
            clearError('selected_product');
            clearError('selected_qty');
            clearError('items');

            const selectEl = document.getElementById('selected_product_id');
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            const prodId = selectEl.value;
            const qtyInput = document.getElementById('selected_qty');
            const qty = parseInt(qtyInput.value) > 0 ? parseInt(qtyInput.value) : 1;

            if (!prodId) {
                return showError('selected_product', 'Please select a product.');
            }

            const name = selectedOption.getAttribute('data-name');
            const code = selectedOption.getAttribute('data-code');
            const price = parseFloat(selectedOption.getAttribute('data-price'));
            const tax = parseFloat(selectedOption.getAttribute('data-tax'));
            const maxStock = parseInt(selectedOption.getAttribute('data-stock'));

            if (qty > maxStock) {
                return showError('selected_qty', `Only ${maxStock} available.`);
            }

            const existing = items.find(i => i.product_id == prodId);
            if (existing) {
                if (existing.quantity + qty > maxStock) {
                    return showError('selected_qty', `Exceeds stock limits.`);
                }
                existing.quantity += qty;
            } else {
                items.push({
                    product_id: parseInt(prodId),
                    name: name,
                    code: code,
                    price_per_unit: price,
                    tax_percentage: tax,
                    max_stock: maxStock,
                    quantity: qty
                });
            }

            selectEl.value = '';
            qtyInput.value = 1;
            renderItemsTable();
        }

        function removeItem(idx) {
            items.splice(idx, 1);
            renderItemsTable();
        }

        function updateItemQty(idx, val) {
            let newQty = parseInt(val);
            if (isNaN(newQty) || newQty < 1) newQty = 1;
            if (newQty > items[idx].max_stock) newQty = items[idx].max_stock;
            items[idx].quantity = newQty;
            renderItemsTable();
        }

        function renderItemsTable() {
            const tbody = document.getElementById('items_table_body');
            if (items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400 font-medium italic">
                            No items added yet. Choose a product above to build the order.
                        </td>
                    </tr>`;
            } else {
                let html = '';
                items.forEach((item, index) => {
                    const lineTotal = (item.price_per_unit * item.quantity) * (1 + item.tax_percentage / 100);
                    html += `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-800">${item.name}</div>
                                <div class="text-xs text-slate-400 font-mono">${item.code}</div>
                            </td>
                            <td class="py-3 px-2 text-center">$${item.price_per_unit.toFixed(2)}</td>
                            <td class="py-3 px-2 text-center text-slate-600">${item.tax_percentage}%</td>
                            <td class="py-3 px-2 text-center">
                                <input type="number" min="1" max="${item.max_stock}" value="${item.quantity}" onchange="updateItemQty(${index}, this.value)" class="w-16 text-center py-1 border border-slate-300 rounded-md text-xs font-semibold">
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-slate-900">$${lineTotal.toFixed(2)}</td>
                            <td class="py-3 px-2 text-center">
                                <button type="button" onclick="removeItem(${index})" class="text-rose-500 hover:text-rose-700 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>`;
                });
                tbody.innerHTML = html;
            }
            recalculate();
        }

        function recalculate() {
            subtotal = items.reduce((sum, i) => sum + (i.price_per_unit * i.quantity), 0);
            taxAmount = items.reduce((sum, i) => sum + ((i.price_per_unit * i.quantity) * (i.tax_percentage / 100)), 0);
            grandTotal = subtotal + taxAmount;

            document.getElementById('subtotal_display').innerText = '$' + subtotal.toFixed(2);
            document.getElementById('tax_display').innerText = '$' + taxAmount.toFixed(2);
            document.getElementById('grand_total_display').innerText = '$' + grandTotal.toFixed(2);

            calculateChange();
        }

        function calculateChange() {
            const amountGiven = parseFloat(document.getElementById('amount_given').value) || 0;
            const balance = (amountGiven >= grandTotal) ? amountGiven - grandTotal : 0;

            document.getElementById('balance_to_return').innerText = '$' + balance.toFixed(2);

            const denomBox = document.getElementById('denomination_box');
            const denomBreakdown = document.getElementById('denomination_breakdown');

            if (balance > 0) {
                let rem = Math.round(balance * 100) / 100;
                const denoms = [100, 50, 20, 10, 5, 1, 0.25, 0.10, 0.05, 0.01];
                let parts = [];
                denoms.forEach(d => {
                    let c = Math.floor(rem / d);
                    if (c > 0) {
                        rem = Math.round((rem - c * d) * 100) / 100;
                        parts.push(`${c}x$${d}`);
                    }
                });
                denomBreakdown.innerText = parts.join(' + ');
                denomBox.classList.remove('hidden');
            } else {
                denomBox.classList.add('hidden');
            }
        }

        function submitOrderViaJQueryAjax() {
            // Clear prior errors
            ['customer_email', 'customer_name', 'items', 'selected_product', 'selected_qty', 'general'].forEach(clearError);

            const customerEmail = document.getElementById('customer_email').value.trim();
            const customerName = document.getElementById('customer_name').value.trim();

            if (!customerEmail) showError('customer_email', 'Customer Email is required.');
            if (!customerName) showError('customer_name', 'Customer Name is required.');
            if (items.length === 0) showError('items', 'Please add at least one product line item.');

            if (!customerEmail || !customerName || items.length === 0) return;

            // Toggle spinner
            document.getElementById('submit_btn').disabled = true;
            document.getElementById('btn_label').classList.add('hidden');
            document.getElementById('btn_spinner').classList.remove('hidden');

            const token = $('meta[name="csrf-token"]').attr('content');
            const payload = {
                customer: { name: customerName, email: customerEmail },
                items: items.map(i => ({ product_id: i.product_id, quantity: i.quantity }))
            };

            $.ajax({
                url: '/api/order',
                type: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                success: function(res) {
                    window.location.href = '/orders/' + res.data.id;
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        for (let k in errors) {
                            showError(k, Array.isArray(errors[k]) ? errors[k][0] : errors[k]);
                        }
                    } else {
                        showError('general', 'Order submission failed. Please try again.');
                    }
                },
                complete: function() {
                    document.getElementById('submit_btn').disabled = false;
                    document.getElementById('btn_label').classList.remove('hidden');
                    document.getElementById('btn_spinner').classList.add('hidden');
                }
            });
        }
    </script>
</x-app-layout>
