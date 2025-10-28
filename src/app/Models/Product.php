<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commodity_id',
        'grade_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'unit',
        'minimum_order',
        'product_photos',
        'status',
        'views_count',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'product_photos' => 'array',
        'views_count' => 'integer',
    ];

    /**
     * Get the commodity that owns the product.
     */
    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    /**
     * Get the grade that owns the product.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(CommodityGrade::class, 'grade_id');
    }

    /**
     * Get the user who created the product.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter only available products.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope to filter public products (available or pre_order).
     */
    public function scopePublic($query)
    {
        return $query->whereIn('status', ['available', 'pre_order']);
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if product can be ordered.
     */
    public function canBeOrdered(): bool
    {
        return in_array($this->status, ['available', 'pre_order']) 
            && ($this->status === 'pre_order' || $this->stock_quantity > 0);
    }
    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['commodity_id', 'grade_id', 'gapoktan_id', 'name', 'price', 'minimum_order', 'stock_quantity', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Product was {$eventName}");
    }
}
