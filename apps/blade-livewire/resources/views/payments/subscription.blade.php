@extends('layouts.app')

@section('title', 'Subscription Demo')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Subscription Billing Demo</h1>
        <p class="text-xl text-gray-600">Choose a plan and start accepting recurring payments</p>
    </div>
    
    @livewire('payment.subscription-form')
</div>
@endsection
