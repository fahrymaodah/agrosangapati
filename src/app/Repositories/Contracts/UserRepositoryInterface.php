<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get active users
     *
     * @return Collection
     */
    public function getActiveUsers(): Collection;

    /**
     * Search users by name or email
     *
     * @param string $search
     * @return Collection
     */
    public function searchUsers(string $search): Collection;
}
