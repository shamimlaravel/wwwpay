<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($plans as $plan)
            <div class="bg-white rounded-xl shadow-sm p-6 border-2 {{ $selectedPlan?->get('id') === $plan['id'] ? 'border-blue-500' : 'border-transparent' }} hover:shadow-lg transition cursor-pointer" wire:click="selectPlan('{{ $plan['id'] }}')">
                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $plan['name'] }}</h3>
                <div class="mb-4">
                    <span class="text-4xl font-bold text-gray-900">${{ $plan['price'] }}</span>
                    <span class="text-gray-500">/month</span>
                </div>
                <ul class="space-y-2 mb-6">
                    @foreach($plan['features'] as $feature)
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
                <button class="w-full {{ $selectedPlan?->get('id') === $plan['id'] ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900' }} py-2 rounded-lg font-medium transition">
                    {{ $selectedPlan?->get('id') === $plan['id'] ? 'Selected' : 'Select Plan' }}
                </button>
            </div>
        @endforeach
    </div>

    @if($selectedPlan)
        <div class="bg-white rounded-xl shadow-sm p-6 max-w-lg mx-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Subscribe to {{ $selectedPlan['name'] }}</h3>
            
            <form wire:submit.prevent="subscribe" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="John Doe">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="john@example.com">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex gap-4 mb-4">
                    <label class="flex items-center">
                        <input type="radio" wire:model="billingCycle" value="monthly" class="mr-2">
                        Monthly
                    </label>
                    <label class="flex items-center">
                        <input type="radio" wire:model="billingCycle" value="yearly" class="mr-2">
                        Yearly <span class="text-green-600 text-sm ml-1">(-20%)</span>
                    </label>
                </div>

                @if($error)
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        {{ $error }}
                    </div>
                @endif

                @if($success)
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        Subscription created! ID: {{ $subscriptionId }}
                    </div>
                @endif

                <button type="submit" wire:loading.attr="disabled" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50">
                    <span wire:loading.remove>Subscribe - ${{ number_format($selectedPlan['price'], 2) }}/{{ $billingCycle === 'yearly' ? 'year' : 'month' }}</span>
                    <span wire:loading>Processing...</span>
                </button>
            </form>
        </div>
    @endif
</div>
