<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier_name',
        'tracking_number',
        'shipping_date',
        'estimated_arrival',
        'actual_arrival',
        'shipment_status',
        'proof_photo',
        'notes',
    ];

    protected $casts = [
        'shipping_date' => 'date',
        'estimated_arrival' => 'date',
        'actual_arrival' => 'date',
    ];

    /**
     * Get the order that owns the shipment
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if shipment is in progress (picked up or in transit)
     */
    public function isInProgress(): bool
    {
        return in_array($this->shipment_status, ['picked_up', 'in_transit']);
    }

    /**
     * Check if shipment is delivered
     */
    public function isDelivered(): bool
    {
        return $this->shipment_status === 'delivered';
    }

    /**
     * Check if shipment can be updated
     */
    public function canBeUpdated(): bool
    {
        return $this->shipment_status !== 'delivered';
    }

    /**
     * Get formatted status text
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->shipment_status) {
            'preparing' => 'Sedang Dipersiapkan',
            'picked_up' => 'Sudah Diambil Kurir',
            'in_transit' => 'Dalam Perjalanan',
            'delivered' => 'Sudah Diterima',
            default => 'Unknown',
        };
    }

    /**
     * Get days until estimated arrival
     */
    public function getDaysUntilArrivalAttribute(): ?int
    {
        if (!$this->estimated_arrival) {
            return null;
        }

        return now()->diffInDays($this->estimated_arrival, false);
    }

    /**
     * Check if shipment is late
     */
    public function isLate(): bool
    {
        if (!$this->estimated_arrival || $this->isDelivered()) {
            return false;
        }

        return now()->isAfter($this->estimated_arrival);
    }
}
