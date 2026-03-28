<?php

namespace ShamimStack\WwwPay\Traits;

use ShamimStack\WwwPay\Models\Subscription;

trait HasSubscriptions
{
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'customer_id');
    }

    public function activeSubscription()
    {
        return $this->subscriptions()->where('status', 'active')->latest()->first();
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'customer_id');
    }

    public function isSubscribed(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    public function subscribe(string $planId, array $data = [])
    {
        $data['plan_id'] = $planId;
        $data['customer_id'] = $this->getKey();
        $data['customer_email'] = $this->email ?? $data['customer_email'] ?? null;

        return Payment::gateway($data['gateway'] ?? null)->subscribe($data);
    }

    public function cancelSubscription()
    {
        if ($this->subscription) {
            return Payment::cancelSubscription($this->subscription->gateway_subscription_id);
        }

        return false;
    }
}
