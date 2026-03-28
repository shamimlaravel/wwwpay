<?php

namespace ShamimStack\WwwPay\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use ShamimStack\WwwPay\Contracts\Subscription as SubscriptionContract;

class Subscription extends Model implements SubscriptionContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gateway',
        'gateway_subscription_id',
        'status',
        'plan_id',
        'customer_id',
        'start_date',
        'end_date',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Check if subscription is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (is_null($this->end_date) || $this->end_date->isFuture());
    }

    /**
     * Check if subscription is cancelled
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if subscription is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return !is_null($this->end_date) && $this->end_date->isPast();
    }

    /**
     * Get the number of days until expiration
     *
     * @return int|null
     */
    public function daysUntilExpiration(): ?int
    {
        if (is_null($this->end_date)) {
            return null;
        }

        return $this->end_date->diffInDays(Carbon::now(), false);
    }

    /**
     * Cancel the subscription
     *
     * @param string|null $reason
     * @return bool
     */
    public function cancel(string $reason = null): bool
    {
        $this->status = 'cancelled';
        $this->end_date = Carbon::now();
        
        if (!is_null($reason)) {
            $this->data = array_merge($this->data ?: [], ['cancellation_reason' => $reason]);
        }
        
        return $this->save();
    }

    public function getGatewaySubscriptionId(): ?string
    {
        return $this->gateway_subscription_id;
    }

    public function getPlanId(): ?string
    {
        return $this->plan_id;
    }

    public function getCustomerId(): ?string
    {
        return $this->customer_id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }
}