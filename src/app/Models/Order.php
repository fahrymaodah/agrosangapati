<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'buyer_address',
        'total_amount',
        'shipping_cost',
        'grand_total',
        'order_status',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    /**
     * Relationship: Order has many items
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship: Order has one shipment
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    /**
     * Scope: Filter by order status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('order_status', $status);
    }

    /**
     * Scope: Filter by payment status
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope: Pending orders (not confirmed yet)
     */
    public function scopePending($query)
    {
        return $query->where('order_status', 'pending');
    }

    /**
     * Scope: Active orders (confirmed, processing, shipped)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('order_status', ['confirmed', 'processing', 'shipped']);
    }

    /**
     * Scope: Completed orders (delivered)
     */
    public function scopeCompleted($query)
    {
        return $query->where('order_status', 'delivered');
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->order_status, ['pending', 'confirmed']);
    }

    /**
     * Check if order can be confirmed
     */
    public function canBeConfirmed(): bool
    {
        return $this->order_status === 'pending';
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Generate order number (format: ORD-YYYYMMDD-XXXX)
     */
    public static function generateOrderNumber(): string
    {
        $date = date('Ymd');
        $lastOrder = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastOrder ? intval(substr($lastOrder->order_number, -4)) + 1 : 1;
        
        return sprintf('ORD-%s-%04d', $date, $sequence);
    }
}
