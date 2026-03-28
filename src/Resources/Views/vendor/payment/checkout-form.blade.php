{{-- Payment Checkout Form Component --}}
{{-- Usage: <x-payment::checkout-form :amount="$amount" :currency="$currency" /> --}}

@props([
    'amount' => 0,
    'currency' => 'USD',
    'gateways' => [],
    'action' => route('payment.checkout'),
    'returnUrl' => route('payment.success'),
    'cancelUrl' => route('payment.cancel'),
])

<div class="max-w-md mx-auto">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-gray-800 text-white px-6 py-4">
            <h3 class="text-lg font-semibold">Complete Payment</h3>
            <p class="text-gray-300 text-sm">Amount: {{ $currency }} {{ number_format($amount, 2) }}</p>
        </div>
        
        <form method="POST" action="{{ $action }}" class="p-6 space-y-6">
            @csrf
            <input type="hidden" name="amount" value="{{ $amount }}">
            <input type="hidden" name="currency" value="{{ $currency }}">
            <input type="hidden" name="return_url" value="{{ $returnUrl }}">
            <input type="hidden" name="cancel_url" value="{{ $cancelUrl }}">
            
            {{-- Payment Gateway Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Select Payment Method</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($gateways as $index => $gateway)
                        <label class="cursor-pointer">
                            <input type="radio" 
                                   name="gateway" 
                                   value="{{ $gateway['key'] ?? $gateway }}" 
                                   class="sr-only peer"
                                   {{ $index === 0 ? 'checked' : '' }}>
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center transition-all
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300">
                                <span class="text-lg">{{ $gateway['icon'] ?? '💳' }}</span>
                                <span class="block text-sm font-medium text-gray-700 mt-1">
                                    {{ is_array($gateway) ? ($gateway['name'] ?? $gateway) : ucfirst($gateway) }}
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            
            {{-- Additional Fields (varies by gateway) --}}
            <div id="additional-fields">
                {{ $slot }}
            </div>
            
            {{-- Submit Button --}}
            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg
                           transition-colors flex items-center justify-center gap-2">
                <span>Pay {{ $currency }} {{ number_format($amount, 2) }}</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
            
            {{-- Security Badge --}}
            <div class="flex items-center justify-center gap-2 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                </svg>
                <span>Secure payment powered by wwwpay</span>
            </div>
        </form>
    </div>
</div>
