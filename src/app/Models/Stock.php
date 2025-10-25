<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'poktan_id',
        'commodity_id',
        'grade_id',
        'quantity',
        'unit',
        'location',
        'last_updated',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the poktan that owns the stock.
     */
    public function poktan(): BelongsTo
    {
        return $this->belongsTo(Poktan::class);
    }

    /**
     * Get the commodity that owns the stock.
     */
    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    /**
     * Get the grade that owns the stock.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(CommodityGrade::class, 'grade_id');
    }

    /**
     * Get all stock movements.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Scope query for specific poktan.
     */
    public function scopeForPoktan($query, int $poktanId)
    {
        return $query->where('poktan_id', $poktanId);
    }

    /**
     * Scope query for specific commodity.
     */
    public function scopeForCommodity($query, int $commodityId)
    {
        return $query->where('commodity_id', $commodityId);
    }

    /**
     * Scope query for low stock (less than minimum).
     */
    public function scopeLowStock($query, float $minimumQuantity = 100)
    {
        return $query->where('quantity', '<', $minimumQuantity);
    }

    /**
     * Scope query by location.
     */
    public function scopeAtLocation($query, ?string $location)
    {
        return $query->where('location', $location);
    }
}
