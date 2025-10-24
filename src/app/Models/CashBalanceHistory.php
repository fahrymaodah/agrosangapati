<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBalanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'poktan_id',
        'transaction_id',
        'previous_balance',
        'amount',
        'new_balance',
        'type',
        'description',
        'created_by',
    ];

    protected $casts = [
        'previous_balance' => 'decimal:2',
        'amount' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the poktan that owns this history record.
     */
    public function poktan(): BelongsTo
    {
        return $this->belongsTo(Poktan::class);
    }

    /**
     * Get the transaction that caused this balance change.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user who created this history record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include history for a specific poktan.
     */
    public function scopeForPoktan($query, $poktanId)
    {
        return $query->where('poktan_id', $poktanId);
    }

    /**
     * Scope a query to only include income changes.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope a query to only include expense changes.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted amount with sign.
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->type === 'income' ? '+' : '-';
        return $sign . ' Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get formatted balance change.
     */
    public function getBalanceChangeAttribute(): string
    {
        return 'Rp ' . number_format($this->previous_balance, 0, ',', '.') . 
               ' → Rp ' . number_format($this->new_balance, 0, ',', '.');
    }
}
