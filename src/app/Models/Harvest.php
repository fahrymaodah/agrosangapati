<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Harvest extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'poktan_id',
        'commodity_id',
        'grade_id',
        'quantity',
        'unit',
        'harvest_date',
        'harvest_photo',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'harvest_date' => 'date',
    ];

    /**
     * Get the member (user) who harvested.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get the poktan.
     */
    public function poktan(): BelongsTo
    {
        return $this->belongsTo(Poktan::class);
    }

    /**
     * Get the commodity.
     */
    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    /**
     * Get the grade.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(CommodityGrade::class, 'grade_id');
    }

    /**
     * Scope to filter by poktan.
     */
    public function scopeForPoktan($query, int $poktanId)
    {
        return $query->where('poktan_id', $poktanId);
    }

    /**
     * Scope to filter by member.
     */
    public function scopeForMember($query, int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('harvest_date', [$startDate, $endDate]);
    }
}
