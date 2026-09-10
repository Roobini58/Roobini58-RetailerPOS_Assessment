<!DOCTYPE html>
<html lang="en" class="h-screen overflow-hidden bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'RetailerPOS — Order & Inventory System' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        @media print {
            html, body {
                height: auto !important;
                min-height: 100% !important;
                overflow: visible !important;
                background: #ffffff !important;
            }
            main {
                overflow: visible !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .max-w-7xl, .max-w-3xl {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="h-screen overflow-hidden flex flex-col antialiased text-slate-800 bg-slate-50">

    @include('layouts.header')

    <!-- Scrollable Main View Content -->
    <main class="flex-1 overflow-y-auto w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Global Toast / Flash Messages -->
            @session('success')
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm print:hidden">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="font-semibold text-sm">{{ $value }}</span>
                    </div>
                </div>
            @endsession

            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>

    @include('layouts.footer')

</body>
</html>
