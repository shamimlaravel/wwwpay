@extends('layouts.app')

@section('title', 'B2B Payments Demo')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">B2B Payments Demo</h1>
        <p class="text-xl text-gray-600">Create invoices and manage business payments</p>
    </div>
    
    @livewire('payment.b2b-invoice')
</div>
@endsection
