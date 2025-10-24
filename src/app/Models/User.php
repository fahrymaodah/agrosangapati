<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'poktan_id',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the poktan that the user belongs to.
     */
    public function poktan(): BelongsTo
    {
        return $this->belongsTo(Poktan::class);
    }

    /**
     * Get the poktan where this user is the chairman.
     */
    public function chairmanOfPoktan(): HasMany
    {
        return $this->hasMany(Poktan::class, 'chairman_id');
    }

    /**
     * Get the gapoktan where this user is the chairman.
     */
    public function chairmanOfGapoktan(): HasMany
    {
        return $this->hasMany(Gapoktan::class, 'chairman_id');
    }

    /**
     * Get all transactions approved by this user.
     */
    public function approvedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'approved_by');
    }

    /**
     * Check if user is superadmin.
     */
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is ketua gapoktan.
     */
    public function isKetuaGapoktan(): bool
    {
        return $this->role === 'ketua_gapoktan';
    }

    /**
     * Check if user is pengurus gapoktan.
     */
    public function isPengurusGapoktan(): bool
    {
        return $this->role === 'pengurus_gapoktan';
    }

    /**
     * Check if user is ketua poktan.
     */
    public function isKetuaPoktan(): bool
    {
        return $this->role === 'ketua_poktan';
    }

    /**
     * Check if user is pengurus poktan.
     */
    public function isPengurusPoktan(): bool
    {
        return $this->role === 'pengurus_poktan';
    }

    /**
     * Check if user is anggota poktan.
     */
    public function isAnggotaPoktan(): bool
    {
        return $this->role === 'anggota_poktan';
    }

    /**
     * Check if user has gapoktan level access.
     */
    public function hasGapoktanAccess(): bool
    {
        return in_array($this->role, ['superadmin', 'ketua_gapoktan', 'pengurus_gapoktan']);
    }

    /**
     * Check if user has poktan level access.
     */
    public function hasPoktanAccess(): bool
    {
        return in_array($this->role, ['ketua_poktan', 'pengurus_poktan']);
    }

    /**
     * Check if user can manage (create/edit/delete) data.
     */
    public function canManageData(): bool
    {
        return !$this->isAnggotaPoktan();
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get users by role.
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get users by poktan.
     */
    public function scopeByPoktan($query, int $poktanId)
    {
        return $query->where('poktan_id', $poktanId);
    }
}
