<div>
    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-6">
        <button wire:click="$set('tab', 'send')" class="px-6 py-3 font-medium {{ $tab === 'send' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            Send Money
        </button>
        <button wire:click="$set('tab', 'request')" class="px-6 py-3 font-medium {{ $tab === 'request' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            Request Money
        </button>
        <button wire:click="$set('tab', 'split')" class="px-6 py-3 font-medium {{ $tab === 'split' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            Split Payment
        </button>
        <button wire:click="$set('tab', 'escrow')" class="px-6 py-3 font-medium {{ $tab === 'escrow' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            Escrow
        </button>
    </div>

    <!-- Send Money -->
    @if($tab === 'send')
        <div class="bg-white rounded-xl shadow-sm p-6 max-w-lg mx-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Send Money</h3>
            <form wire:submit.prevent="sendMoney" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recipient ID</label>
                    <input type="text" wire:model="recipientId" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="user_123">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                        <input type="number" step="0.01" wire:model="amount" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                        <select wire:model="currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="NGN">NGN</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note (Optional)</label>
                    <input type="text" wire:model="note" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="For dinner...">
                </div>
                @include('livewire.payment.partials.submit-btn')
            </form>
        </div>
    @endif

    <!-- Request Money -->
    @if($tab === 'request')
        <div class="bg-white rounded-xl shadow-sm p-6 max-w-lg mx-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Request Money</h3>
            <form wire:submit.prevent="requestMoney" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From User</label>
                    <input type="text" wire:model="recipientId" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="user_123">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                        <input type="number" step="0.01" wire:model="amount" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                        <select wire:model="currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                </div>
                @include('livewire.payment.partials.submit-btn')
            </form>
        </div>
    @endif

    <!-- Split Payment -->
    @if($tab === 'split')
        <div class="bg-white rounded-xl shadow-sm p-6 max-w-lg mx-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Split Payment</h3>
            <form wire:submit.prevent="splitPayment" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount</label>
                    <input type="number" step="0.01" wire:model="splitAmount" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Add Participants</label>
                    <div class="flex gap-2">
                        <input type="text" wire:model="newParticipant" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg" placeholder="user_id">
                        <button type="button" wire:click="addParticipant" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">Add</button>
                    </div>
                </div>
                <div class="space-y-2">
                    @foreach($splitParticipants as $index => $participant)
                        <div class="flex justify-between items-center bg-gray-50 px-4 py-2 rounded-lg">
                            <span>{{ $participant }}</span>
                            <button type="button" wire:click="removeParticipant({{ $index }})" class="text-red-500 hover:text-red-700">Remove</button>
                        </div>
                    @endforeach
                </div>
                @if(count($splitParticipants) > 0)
                    <p class="text-sm text-gray-500">Each participant pays: ${{ number_format($splitAmount / count($splitParticipants), 2) }}</p>
                @endif
                @include('livewire.payment.partials.submit-btn')
            </form>
        </div>
    @endif

    <!-- Escrow -->
    @if($tab === 'escrow')
        <div class="bg-white rounded-xl shadow-sm p-6 max-w-lg mx-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Create Escrow</h3>
            <form wire:submit.prevent="createEscrow" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <input type="number" step="0.01" wire:model="escrowAmount" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Release To (User ID)</label>
                    <input type="text" wire:model="releasedTo" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="seller_user_id">
                </div>
                <p class="text-sm text-gray-500">Funds will be held until you confirm delivery</p>
                @include('livewire.payment.partials.submit-btn')
            </form>
        </div>
    @endif

    <!-- Success/Error Messages -->
    @if($success && $transactionId)
        <div class="mt-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg max-w-lg mx-auto">
            Success! Transaction ID: {{ $transactionId }}
        </div>
    @endif
    
    @if($error)
        <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg max-w-lg mx-auto">
            {{ $error }}
        </div>
    @endif
</div>
