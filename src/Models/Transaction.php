<?php

namespace ShamimStack\AllInOnePayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Transaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'gateway',
        'gateway_transaction_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'type',
        'customer_id',
        'customer_email',
        'description',
        'metadata',
        'response_data',
        'error_message',
        'refunded_amount',
        'refunded_at',
        'cancelled_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'metadata' => 'array',
        'response_data' => 'array',
        'refunded_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    public const TYPE_PAYMENT = 'payment';
    public const TYPE_REFUND = 'refund';
    public const TYPE_SUBSCRIPTION = 'subscription';

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'customer_id', 'customer_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Transaction::class, 'gateway_transaction_id', 'gateway_transaction_id')
            ->where('type', self::TYPE_REFUND);
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function markAsCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = Carbon::now();
        return $this->save();
    }

    public function markAsFailed(string $errorMessage = null): bool
    {
        $this->status = self::STATUS_FAILED;
        if ($errorMessage) {
            $this->error_message = $errorMessage;
        }
        return $this->save();
    }

    public function markAsProcessing(): bool
    {
        $this->status = self::STATUS_PROCESSING;
        return $this->save();
    }

    public function processRefund(float $amount = null): bool
    {
        $refundAmount = $amount ?? $this->amount;
        
        $newRefundedAmount = ($this->refunded_amount ?? 0) + $refundAmount;
        
        if ($newRefundedAmount >= $this->amount) {
            $this->status = self::STATUS_REFUNDED;
            $this->refunded_amount = $this->amount;
        } else {
            $this->status = self::STATUS_PARTIALLY_REFUNDED;
            $this->refunded_amount = $newRefundedAmount;
        }
        
        $this->refunded_at = Carbon::now();
        return $this->save();
    }

    public function cancel(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = Carbon::now();
        return $this->save();
    }

    public function getRefundableAmount(): float
    {
        return max(0, ($this->amount ?? 0) - ($this->refunded_amount ?? 0));
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByCustomer($query, string $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
