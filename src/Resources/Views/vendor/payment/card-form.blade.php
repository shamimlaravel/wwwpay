{{-- Credit Card Input Form Component --}}
{{-- Usage: <x-payment::card-form /> --}}

@props([
    'prefix' => 'card',
])

<div class="space-y-4" x-data="cardForm()">
    {{-- Card Number --}}
    <div>
        <label for="{{ $prefix }}_number" class="block text-sm font-medium text-gray-700">Card Number</label>
        <div class="mt-1 relative">
            <input type="text" 
                   id="{{ $prefix }}_number" 
                   name="{{ $prefix }}_number"
                   x-model="cardNumber"
                   @input="formatCardNumber($event)"
                   @blur="detectCardType"
                   placeholder="4242 4242 4242 4242"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-3 border"
                   maxlength="19"
                   autocomplete="cc-number">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <span x-text="cardIcon" class="text-2xl"></span>
            </div>
        </div>
        <p x-show="cardType" x-text="'Card Type: ' + cardType" class="mt-1 text-xs text-gray-500"></p>
    </div>

    {{-- Card Holder Name --}}
    <div>
        <label for="{{ $prefix }}_name" class="block text-sm font-medium text-gray-700">Cardholder Name</label>
        <input type="text" 
               id="{{ $prefix }}_name" 
               name="{{ $prefix }}_name"
               placeholder="John Doe"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-3 border"
               autocomplete="cc-name">
    </div>

    {{-- Expiry and CVV --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="{{ $prefix }}_expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
            <input type="text" 
                   id="{{ $prefix }}_expiry" 
                   name="{{ $prefix }}_expiry"
                   x-model="expiry"
                   @input="formatExpiry($event)"
                   placeholder="MM/YY"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-3 border"
                   maxlength="5"
                   autocomplete="cc-exp">
        </div>
        <div>
            <label for="{{ $prefix }}_cvc" class="block text-sm font-medium text-gray-700">CVC</label>
            <input type="text" 
                   id="{{ $prefix }}_cvc" 
                   name="{{ $prefix }}_cvc"
                   x-model="cvc"
                   placeholder="123"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-3 border"
                   maxlength="4"
                   autocomplete="cc-csc">
        </div>
    </div>
</div>

@push('scripts')
<script>
function cardForm() {
    return {
        cardNumber: '',
        cardType: '',
        expiry: '',
        cvc: '',
        cardIcon: '💳',
        
        formatCardNumber(event) {
            let value = event.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
            this.cardNumber = formatted.substring(0, 19);
            event.target.value = this.cardNumber;
        },
        
        formatExpiry(event) {
            let value = event.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.expiry = value;
            event.target.value = this.expiry;
        },
        
        detectCardType() {
            const number = this.cardNumber.replace(/\s/g, '');
            
            if (/^4/.test(number)) {
                this.cardType = 'Visa';
                this.cardIcon = '💳';
            } else if (/^5[1-5]/.test(number)) {
                this.cardType = 'Mastercard';
                this.cardIcon = '💳';
            } else if (/^3[47]/.test(number)) {
                this.cardType = 'American Express';
                this.cardIcon = '💳';
            } else if (/^6(?:011|5)/.test(number)) {
                this.cardType = 'Discover';
                this.cardIcon = '💳';
            } else {
                this.cardType = '';
                this.cardIcon = '💳';
            }
        }
    }
}
</script>
@endpush
