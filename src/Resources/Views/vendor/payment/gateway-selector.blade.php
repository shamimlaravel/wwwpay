{{-- Payment Gateway Selection Component --}}
{{-- Usage: <x-payment::gateway-selector /> --}}

<div class="payment-gateway-selector" data-gateways="{{ json_encode($gateways ?? []) }}">
    <label class="block text-sm font-medium text-gray-700 mb-2">Select Payment Method</label>
    
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        @foreach($gateways ?? [] as $gateway)
            <label class="gateway-option cursor-pointer">
                <input type="radio" 
                       name="payment_gateway" 
                       value="{{ $gateway['key'] }}" 
                       class="sr-only peer"
                       {{ $loop->first ? 'checked' : '' }}>
                <div class="border-2 border-gray-200 rounded-lg p-3 text-center transition-all
                            peer-checked:border-blue-500 peer-checked:bg-blue-50
                            hover:border-gray-300">
                    <div class="text-2xl mb-1">{{ $gateway['icon'] ?? '💳' }}</div>
                    <div class="text-sm font-medium text-gray-700">{{ $gateway['name'] }}</div>
                    <div class="text-xs text-gray-500">{{ $gateway['region'] ?? '' }}</div>
                </div>
            </label>
        @endforeach
    </div>
    
    @error('payment_gateway')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@push('styles')
<style>
.payment-gateway-selector .gateway-option input:checked + div {
    border-color: #3b82f6;
    background-color: #eff6ff;
}
</style>
@endpush
