@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="max-w-md mx-auto px-4 py-16 text-center">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
        <p class="text-gray-600 mb-6">Thank you for your purchase. Your order has been confirmed.</p>
        
        @if(isset($transactionId))
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-500">Transaction ID</p>
                <p class="font-mono font-medium text-gray-900">{{ $transactionId }}</p>
            </div>
        @endif
        
        <div class="flex flex-col gap-3">
            <a href="/" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                Continue Shopping
            </a>
            <a href="/orders" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                View Orders
            </a>
        </div>
    </div>
    
    <p class="text-center text-gray-500 text-sm mt-8">
        Payment processed securely by <span class="font-semibold">WwwPay</span>
    </p>
</div>
@endsection
