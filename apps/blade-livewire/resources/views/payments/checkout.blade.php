@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            @livewire('payment.checkout')
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                <h3 class="font-bold text-gray-900 mb-4">Your Cart</h3>
                
                @livewire('store.cart-manager')
                
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex justify-between text-sm text-gray-600 py-2">
                        <span>Subtotal</span>
                        <span>${{ number_format($total ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600 py-2">
                        <span>Shipping</span>
                        <span class="text-green-600">Free</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-4 border-t border-gray-200">
                        <span>Total</span>
                        <span class="text-blue-600">${{ number_format($total ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
