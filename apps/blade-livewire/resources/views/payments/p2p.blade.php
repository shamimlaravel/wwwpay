@extends('layouts.app')

@section('title', 'P2P Payments Demo')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">P2P Payments Demo</h1>
        <p class="text-xl text-gray-600">Send money, split bills, and use escrow</p>
    </div>
    
    @livewire('payment.p2p-transfer')
</div>
@endsection
