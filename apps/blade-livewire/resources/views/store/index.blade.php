@extends('layouts.app')

@section('title', 'Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 mb-8 text-white">
        <h1 class="text-4xl font-bold mb-2">Welcome to Demo Store</h1>
        <p class="text-blue-100 mb-6">Shop with 33+ payment methods worldwide</p>
        <div class="flex gap-4">
            <a href="#products" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
                Browse Products
            </a>
            <a href="{{ route('payment.demo') }}" class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition">
                View Payment Demos
            </a>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Accepted Payment Methods</h2>
        <div class="flex flex-wrap gap-3">
            @foreach(['Stripe', 'PayPal', 'bKash', 'Paystack', 'Flutterwave', 'Alipay', 'WeChat', 'Mada'] as $gateway)
                <span class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full text-sm font-medium">
                    {{ $gateway }}
                </span>
            @endforeach
            <span class="text-gray-400 px-4 py-2 text-sm">+ 25 more</span>
        </div>
    </div>

    <!-- Products -->
    <div id="products">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Featured Products</h2>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">All</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Electronics</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Clothing</button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products ?? [] as $product)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition group">
                    <div class="aspect-square bg-gray-100 flex items-center justify-center">
                        <span class="text-6xl">{{ $product['icon'] ?? '📦' }}</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition">
                            {{ $product['name'] }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $product['description'] ?? '' }}</p>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xl font-bold text-gray-900">
                                ${{ number_format($product['price'], 2) }}
                            </span>
                            <button 
                                wire:click="addToCart('{{ $product['id'] }}')"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Sample Products (if no products passed) -->
            @if(!isset($products))
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition group">
                    <div class="aspect-square bg-gray-100 flex items-center justify-center">
                        <span class="text-6xl">📱</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition">Smart Watch Pro</h3>
                        <p class="text-sm text-gray-500 mt-1">Latest smartwatch with health tracking</p>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xl font-bold text-gray-900">$299.00</span>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition group">
                    <div class="aspect-square bg-gray-100 flex items-center justify-center">
                        <span class="text-6xl">🎧</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition">Wireless Headphones</h3>
                        <p class="text-sm text-gray-500 mt-1">Premium noise-canceling headphones</p>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xl font-bold text-gray-900">$199.00</span>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition group">
                    <div class="aspect-square bg-gray-100 flex items-center justify-center">
                        <span class="text-6xl">💻</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition">Ultra Laptop</h3>
                        <p class="text-sm text-gray-500 mt-1">Powerful laptop for professionals</p>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xl font-bold text-gray-900">$1299.00</span>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition group">
                    <div class="aspect-square bg-gray-100 flex items-center justify-center">
                        <span class="text-6xl">⌚</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition">Classic Watch</h3>
                        <p class="text-sm text-gray-500 mt-1">Elegant timepiece for any occasion</p>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xl font-bold text-gray-900">$449.00</span>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">Add to Cart</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Demo Links -->
    <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('payment.subscription') }}" class="bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl p-6 text-white hover:shadow-lg transition">
            <span class="text-4xl mb-4 block">🔄</span>
            <h3 class="text-xl font-bold mb-2">Subscription Billing</h3>
            <p class="text-purple-100">Create recurring payment plans</p>
        </a>
        <a href="{{ route('payment.p2p') }}" class="bg-gradient-to-br from-green-500 to-teal-500 rounded-xl p-6 text-white hover:shadow-lg transition">
            <span class="text-4xl mb-4 block">💸</span>
            <h3 class="text-xl font-bold mb-2">P2P Transfers</h3>
            <p class="text-green-100">Send money to friends</p>
        </a>
        <a href="{{ route('payment.b2b') }}" class="bg-gradient-to-br from-orange-500 to-red-500 rounded-xl p-6 text-white hover:shadow-lg transition">
            <span class="text-4xl mb-4 block">🏢</span>
            <h3 class="text-xl font-bold mb-2">B2B Invoicing</h3>
            <p class="text-orange-100">Create business invoices</p>
        </a>
    </div>
</div>
@endsection
