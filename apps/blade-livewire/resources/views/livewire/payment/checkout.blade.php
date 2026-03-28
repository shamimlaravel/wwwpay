<div>
    <!-- Progress Steps -->
    <div class="flex items-center justify-center mb-8">
        <div class="flex items-center gap-4">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ $step >= 1 ? 'bg-blue-600' : 'bg-gray-200' }} flex items-center justify-center text-white font-medium">1</div>
                <span class="ml-2 text-sm {{ $step >= 1 ? 'text-blue-600' : 'text-gray-500' }}">Gateway</span>
            </div>
            <div class="w-12 h-0.5 bg-gray-200"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ $step >= 2 ? 'bg-blue-600' : 'bg-gray-200' }} flex items-center justify-center text-white font-medium">2</div>
                <span class="ml-2 text-sm {{ $step >= 2 ? 'text-blue-600' : 'text-gray-500' }}">Payment</span>
            </div>
            <div class="w-12 h-0.5 bg-gray-200"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ $step >= 3 ? 'bg-blue-600' : 'bg-gray-200' }} flex items-center justify-center text-white font-medium">3</div>
                <span class="ml-2 text-sm {{ $step >= 3 ? 'text-blue-600' : 'text-gray-500' }}">Done</span>
            </div>
        </div>
    </div>

    <!-- Step 1: Gateway Selection -->
    @if($step === 1)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Select Payment Method</h2>

            <!-- Cart Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-700 mb-3">Order Summary</h3>
                @foreach($cart as $item)
                    <div class="flex justify-between text-sm py-2 border-b border-gray-200 last:border-0">
                        <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                        <span class="font-medium">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between text-lg font-bold pt-3">
                    <span>Total</span>
                    <span class="text-blue-600">${{ number_format($total, 2) }} {{ $currency }}</span>
                </div>
            </div>

            <!-- Gateway Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                @foreach($gateways as $gateway)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="selectedGateway" value="{{ $gateway['key'] }}" class="sr-only peer">
                        <div class="border-2 border-gray-200 rounded-xl p-4 text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300">
                            <span class="text-3xl mb-2 block">{{ $gateway['icon'] }}</span>
                            <span class="block font-medium text-gray-900">{{ $gateway['name'] }}</span>
                            <span class="text-xs text-gray-500">{{ $gateway['region'] }}</span>
                        </div>
                    </label>
                @endforeach
            </div>

            <!-- Email Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" wire:model="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="your@email.com">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <button wire:click="nextStep" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                Continue to Payment
            </button>
        </div>
    @endif

    <!-- Step 2: Payment -->
    @if($step === 2)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Complete Payment</h2>
                <button wire:click="prevStep" class="text-blue-600 hover:text-blue-700">← Back</button>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total to Pay</span>
                    <span class="text-blue-600">${{ number_format($total, 2) }} {{ $currency }}</span>
                </div>
            </div>

            <!-- Card Form (for card payments) -->
            @if(in_array($selectedGateway, ['stripe', 'square', 'authorize']))
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
                        <input type="text" wire:model="cardNumber" maxlength="19" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="4242 4242 4242 4242">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry</label>
                            <input type="text" wire:model="cardExpiry" maxlength="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="MM/YY">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CVC</label>
                            <input type="text" wire:model="cardCvc" maxlength="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="123">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cardholder Name</label>
                        <input type="text" wire:model="cardName" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="John Doe">
                    </div>
                </div>
            @endif

            @if($selectedGateway === 'paystack')
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" wire:model="phone" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="+234 800 000 0000">
                </div>
            @endif

            @if($error)
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    {{ $error }}
                </div>
            @endif

            <button wire:click="processPayment" wire:loading.attr="disabled" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50">
                <span wire:loading.remove>Pay ${{ number_format($total, 2) }}</span>
                <span wire:loading>Processing...</span>
            </button>

            <div class="flex items-center justify-center gap-2 mt-4 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                </svg>
                Secure payment powered by WwwPay
            </div>
        </div>
    @endif

    <!-- Step 3: Success -->
    @if($step === 3)
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
            <p class="text-gray-600 mb-6">Thank you for your purchase</p>
            <p class="text-sm text-gray-500 mb-6">Transaction ID: <span class="font-mono font-medium">{{ $transactionId }}</span></p>
            <div class="flex gap-4 justify-center">
                <a href="/" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Continue Shopping
                </a>
                <a href="/orders" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                    View Orders
                </a>
            </div>
        </div>
    @endif
</div>
