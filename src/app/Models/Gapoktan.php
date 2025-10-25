<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gapoktan extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gapoktan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'address',
        'village',
        'district',
        'province',
        'chairman_id',
        'phone',
        'email',
        'established_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'established_date' => 'date',
    ];

    /**
     * Get the nama attribute (alias for name).
     */
    public function getNamaAttribute(): ?string
    {
        return $this->name;
    }

    /**
     * Get the chairman (Ketua Gapoktan).
     */
    public function chairman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chairman_id');
    }

    /**
     * Get all poktans under this gapoktan.
     * Note: In reality, this would need a gapoktan_id in poktans table
     * For now, we'll assume all poktans belong to the same gapoktan.
     */
    public function poktans(): HasMany
    {
        return $this->hasMany(Poktan::class, 'gapoktan_id');
    }

    /**
     * Get all pengurus (management) of this gapoktan.
     */
    public function management(): HasMany
    {
        return $this->hasMany(User::class)->whereIn('role', ['ketua_gapoktan', 'pengurus_gapoktan']);
    }
}
