@extends('layouts.app')

@section('title', 'Payment Cancelled')

@section('content')
<div class="max-w-md mx-auto px-4 py-16 text-center">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Cancelled</h1>
        <p class="text-gray-600 mb-6">
            {{ $error ?? 'Your payment was cancelled. No charges were made.' }}
        </p>
        
        <div class="flex flex-col gap-3">
            <a href="/checkout" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                Try Again
            </a>
            <a href="/" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                Back to Store
            </a>
        </div>
    </div>
</div>
@endsection
