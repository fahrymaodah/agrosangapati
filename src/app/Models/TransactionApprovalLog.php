<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'action',
        'previous_status',
        'new_status',
        'performed_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the transaction that this log belongs to.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope for approval actions.
     */
    public function scopeApprovals($query)
    {
        return $query->where('action', 'approved');
    }

    /**
     * Scope for rejection actions.
     */
    public function scopeRejections($query)
    {
        return $query->where('action', 'rejected');
    }

    /**
     * Scope for a specific transaction.
     */
    public function scopeForTransaction($query, int $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    /**
     * Scope for a specific performer.
     */
    public function scopeByPerformer($query, int $userId)
    {
        return $query->where('performed_by', $userId);
    }

    /**
     * Get formatted action.
     */
    public function getFormattedActionAttribute(): string
    {
        return ucfirst($this->action);
    }

    /**
     * Get action with status change.
     */
    public function getActionSummaryAttribute(): string
    {
        if ($this->previous_status) {
            return "{$this->formatted_action}: {$this->previous_status} → {$this->new_status}";
        }
        
        return "{$this->formatted_action}: {$this->new_status}";
    }
}
