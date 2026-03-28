{{-- Payment Status Badge Component --}}
{{-- Usage: <x-payment::status-badge :status="$status" /> --}}

@props([
    'status' => 'pending',
])

@php
$statusConfig = [
    'pending' => ['color' => 'yellow', 'icon' => '⏳', 'text' => 'Pending'],
    'success' => ['color' => 'green', 'icon' => '✓', 'text' => 'Success'],
    'failed' => ['color' => 'red', 'icon' => '✗', 'text' => 'Failed'],
    'cancelled' => ['color' => 'gray', 'icon' => '⊘', 'text' => 'Cancelled'],
    'refunded' => ['color' => 'blue', 'icon' => '↩', 'text' => 'Refunded'],
    'processing' => ['color' => 'purple', 'icon' => '◐', 'text' => 'Processing'],
];

$config = $statusConfig[$status] ?? $statusConfig['pending'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
              bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
    <span class="mr-1">{{ $config['icon'] }}</span>
    {{ $config['text'] }}
</span>
