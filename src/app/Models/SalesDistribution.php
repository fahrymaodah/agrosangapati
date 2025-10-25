<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'poktan_id',
        'commodity_id',
        'quantity_sold',
        'sale_price',
        'total_revenue',
        'gapoktan_margin',
        'poktan_payment',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'quantity_sold' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'gapoktan_margin' => 'decimal:2',
        'poktan_payment' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the order item that owns the distribution
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the poktan that receives the payment
     */
    public function poktan(): BelongsTo
    {
        return $this->belongsTo(Poktan::class);
    }

    /**
     * Get the commodity
     */
    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Check if payment is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Get formatted status text
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Sudah Dibayar',
            default => 'Unknown',
        };
    }

    /**
     * Get profit margin percentage
     */
    public function getMarginPercentageAttribute(): float
    {
        if ($this->total_revenue == 0) {
            return 0;
        }

        return ($this->gapoktan_margin / $this->total_revenue) * 100;
    }

    /**
     * Scope query for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope query for paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope query by poktan
     */
    public function scopeByPoktan($query, int $poktanId)
    {
        return $query->where('poktan_id', $poktanId);
    }
}
