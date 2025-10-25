<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommodityGrade extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commodity_id',
        'grade_name',
        'price_modifier',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_modifier' => 'decimal:2',
    ];

    /**
     * Get the commodity that owns the grade.
     */
    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    /**
     * Get the harvests for the grade.
     */
    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'grade_id');
    }

    /**
     * Get the stocks for the grade.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'grade_id');
    }

    /**
     * Calculate the actual price based on commodity market price and modifier.
     *
     * @return float
     */
    public function getActualPriceAttribute(): float
    {
        if (!$this->commodity) {
            return 0;
        }

        $basePrice = $this->commodity->current_market_price;
        $modifier = $this->price_modifier / 100; // Convert percentage to decimal
        
        return $basePrice + ($basePrice * $modifier);
    }
}
