<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poktan extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'village',
        'gapoktan_id',
        'chairman_id',
        'total_members',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_members' => 'integer',
    ];

    /**
     * Get the gapoktan that owns this poktan.
     */
    public function gapoktan(): BelongsTo
    {
        return $this->belongsTo(Gapoktan::class, 'gapoktan_id');
    }

    /**
     * Get the chairman (Ketua Poktan) of this poktan.
     */
    public function chairman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chairman_id');
    }

    /**
     * Get all members of this poktan.
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'poktan_id');
    }

    /**
     * Get all transactions for this poktan.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'poktan_id');
    }

    /**
     * Get the cash balance for this poktan.
     */
    public function cashBalance(): HasMany
    {
        return $this->hasMany(CashBalance::class, 'poktan_id');
    }

    /**
     * Get all harvests from this poktan.
     */
    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'poktan_id');
    }

    /**
     * Get all stocks for this poktan.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'poktan_id');
    }

    /**
     * Scope to get only active poktans.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
