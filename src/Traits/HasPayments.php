<?php

namespace ShamimStack\WwwPay\Traits;

use ShamimStack\WwwPay\Models\Transaction;
use ShamimStack\WwwPay\Facades\Payment;

trait HasPayments
{
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'customer');
    }

    public function payments()
    {
        return $this->transactions()->where('type', Transaction::TYPE_PAYMENT);
    }

    public function refunds()
    {
        return $this->transactions()->where('type', Transaction::TYPE_REFUND);
    }

    public function successfulPayments()
    {
        return $this->payments()->where('status', Transaction::STATUS_COMPLETED);
    }

    public function totalSpent(): float
    {
        return $this->successfulPayments()->sum('amount');
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    public function processPayment(array $data)
    {
        $data['customer_id'] = $this->getKey();
        $data['customer_email'] = $this->email ?? $data['email'] ?? null;

        return Payment::pay($data);
    }
}
