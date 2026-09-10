<!-- Fixed Top Navigation Header -->
<header class="shrink-0 bg-slate-900 text-white shadow-md border-b border-slate-800 z-30 print:hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo & Brand -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-emerald-400 flex items-center justify-center font-bold text-white shadow-lg">
                    POS
                </div>
                <div>
                    <span class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">
                        RetailerPOS
                    </span>
                    <span class="text-xs block text-slate-400 font-medium">Inventory & Billing System</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex items-center space-x-2">
                <a href="{{ route('orders.create') }}" 
                   @class([
                       'px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-150',
                       'bg-indigo-600 text-white shadow-sm' => request()->routeIs('orders.create'),
                       'text-slate-300 hover:text-white hover:bg-slate-800' => !request()->routeIs('orders.create'),
                   ])>
                    ⚡ New Order
                </a>
                <a href="{{ route('orders.index') }}" 
                   @class([
                       'px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-150',
                       'bg-indigo-600 text-white shadow-sm' => request()->routeIs('orders.index'),
                       'text-slate-300 hover:text-white hover:bg-slate-800' => !request()->routeIs('orders.index'),
                   ])>
                    📋 Order History
                </a>
            </nav>
        </div>
    </div>
</header>
