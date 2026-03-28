<div>
    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-6">
        <button wire:click="$set('tab', 'invoice')" class="px-6 py-3 font-medium {{ $tab === 'invoice' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            Create Invoice
        </button>
        <button wire:click="$set('tab', 'wire')" class="px-6 py-3 font-medium {{ $tab === 'wire' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            Wire Transfer
        </button>
    </div>

    <!-- Invoice Form -->
    @if($tab === 'invoice')
        <div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl mx-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Create Invoice</h3>
            
            <form wire:submit.prevent="createInvoice" class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
                        <input type="text" wire:model="clientName" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('clientName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client Email</label>
                        <input type="email" wire:model="clientEmail" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('clientEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Items -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Line Items</label>
                    <div class="space-y-3">
                        @foreach($items as $index => $item)
                            <div class="flex gap-3 items-start bg-gray-50 p-3 rounded-lg">
                                <input type="text" wire:model="items.{{ $index }}.description" placeholder="Description" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input type="number" wire:model="items.{{ $index }}.quantity" placeholder="Qty" class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.price" placeholder="Price" class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <button type="button" wire:click="removeItem('{{ $item['id'] }}')" class="text-red-500 hover:text-red-700 p-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" wire:click="addItem" class="mt-3 text-blue-600 hover:text-blue-700 text-sm font-medium">
                        + Add Item
                    </button>
                </div>

                <!-- Totals -->
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Tax (10%)</span>
                        <span>${{ number_format($tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                        <span>Total</span>
                        <span class="text-blue-600">${{ number_format($total, 2) }}</span>
                    </div>
                </div>

                @include('livewire.payment.partials.submit-btn')
            </form>
        </div>
    @endif

    <!-- Wire Transfer -->
    @if($tab === 'wire')
        <div class="bg-white rounded-xl shadow-sm p-6 max-w-lg mx-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Wire Transfer</h3>
            
            <form wire:submit.prevent="createWireTransfer" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Beneficiary Name</label>
                    <input type="text" wire:model="clientName" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Beneficiary Email</label>
                    <input type="email" wire:model="clientEmail" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                    <select wire:model="currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="GBP">GBP</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <input type="number" step="0.01" wire:model="total" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                
                <p class="text-sm text-gray-500">Bank transfer typically takes 2-5 business days</p>
                
                @include('livewire.payment.partials.submit-btn')
            </form>
        </div>
    @endif

    @if($success && $invoiceId)
        <div class="mt-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg max-w-lg mx-auto">
            Invoice created! ID: {{ $invoiceId }}
        </div>
    @endif
    
    @if($error)
        <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg max-w-lg mx-auto">
            {{ $error }}
        </div>
    @endif
</div>
