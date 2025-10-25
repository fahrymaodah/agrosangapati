<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionCategory extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'is_default',
        'poktan_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the poktan that owns this category.
     * Null if it's a default category for all poktans.
     */
    public function poktan(): BelongsTo
    {
        return $this->belongsTo(Poktan::class);
    }

    /**
     * Get all transactions using this category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    /**
     * Scope to get only income categories.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope to get only expense categories.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope to get default categories (available for all poktans).
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true)->whereNull('poktan_id');
    }

    /**
     * Scope to get custom categories for a specific poktan.
     */
    public function scopeForPoktan($query, int $poktanId)
    {
        return $query->where('poktan_id', $poktanId);
    }

    /**
     * Scope to get all available categories for a poktan (default + custom).
     */
    public function scopeAvailableForPoktan($query, int $poktanId)
    {
        return $query->where(function ($q) use ($poktanId) {
            $q->whereNull('poktan_id')  // Default categories
              ->orWhere('poktan_id', $poktanId);  // Custom categories for this poktan
        });
    }
}
