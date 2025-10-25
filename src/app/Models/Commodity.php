<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commodity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'unit',
        'current_market_price',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_market_price' => 'decimal:2',
    ];

    /**
     * Get the grades for the commodity.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(CommodityGrade::class);
    }

    /**
     * Get the harvests for the commodity.
     */
    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    /**
     * Get the stocks for the commodity.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Get the products for the commodity.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
