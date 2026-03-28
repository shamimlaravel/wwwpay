<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Payment') - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('styles')
    @stack('scripts')
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center gap-2">
                        <span class="text-2xl">💳</span>
                        <span class="font-bold text-xl text-gray-900">WwwPay</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('payment.checkout') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2">
                        Checkout
                    </a>
                    <a href="{{ route('payment.history') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2">
                        History
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="py-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 text-gray-500 text-sm">
                    <span class="text-lg">💳</span>
                    <span>Powered by WwwPay - All-in-One Payment Solution</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-gray-400 hover:text-gray-600 text-sm">Documentation</a>
                    <a href="#" class="text-gray-400 hover:text-gray-600 text-sm">Support</a>
                    <a href="#" class="text-gray-400 hover:text-gray-600 text-sm">Privacy</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('page-scripts')
</body>
</html>
