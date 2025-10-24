<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Make model instance
     *
     * @return Model
     */
    protected function makeModel(): Model
    {
        return new User();
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Get active users
     * 
     * Note: Assuming users table has 'is_active' or 'status' column
     * Adjust according to your actual table structure
     *
     * @return Collection
     */
    public function getActiveUsers(): Collection
    {
        // If you have 'email_verified_at' as indicator of active user
        return $this->model->whereNotNull('email_verified_at')->get();
        
        // Or if you have 'status' column:
        // return $this->model->where('status', 'active')->get();
    }

    /**
     * Search users by name or email
     *
     * @param string $search
     * @return Collection
     */
    public function searchUsers(string $search): Collection
    {
        return $this->model
            ->where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->get();
    }
}
