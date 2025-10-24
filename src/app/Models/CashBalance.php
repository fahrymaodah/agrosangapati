<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'poktan_id',
        'balance',
        'last_updated',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the poktan that owns this cash balance.
     */
    public function poktan(): BelongsTo
    {
        return $this->belongsTo(Poktan::class);
    }

    /**
     * Get the history records for this cash balance.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(CashBalanceHistory::class, 'poktan_id', 'poktan_id');
    }
}
