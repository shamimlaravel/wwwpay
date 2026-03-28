<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'currency',
        'status',
        'gateway',
        'transaction_id',
        'items',
        'customer_name',
        'customer_email',
        'shipping_address',
        'billing_address',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'items' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CANCELLED = 'cancelled';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return OrderItem::where('order_id', $this->id)->get();
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function canRefund(): bool
    {
        return $this->isPaid();
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'bg-green-100 text-green-800',
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_FAILED => 'bg-red-100 text-red-800',
            self::STATUS_REFUNDED => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
