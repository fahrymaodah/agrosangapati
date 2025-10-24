<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepository;

    /**
     * UserService constructor
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users
     *
     * @return Collection
     */
    public function getAllUsers(): Collection
    {
        return $this->userRepository->all();
    }

    /**
     * Get paginated users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Create new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        // Business logic: Hash password before storing
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Additional business logic can be added here
        // e.g., send welcome email, create default settings, etc.

        return $this->userRepository->create($data);
    }

    /**
     * Update user
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser(int $id, array $data): bool
    {
        // Business logic: Hash password if being updated
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        // Additional business logic can be added here
        // e.g., cleanup user data, notify admin, etc.

        return $this->userRepository->delete($id);
    }

    /**
     * Get active users
     *
     * @return Collection
     */
    public function getActiveUsers(): Collection
    {
        return $this->userRepository->getActiveUsers();
    }

    /**
     * Search users
     *
     * @param string $search
     * @return Collection
     */
    public function searchUsers(string $search): Collection
    {
        // Business logic: Validate search term length
        if (strlen($search) < 2) {
            return collect();
        }

        return $this->userRepository->searchUsers($search);
    }

    /**
     * Check if email already exists
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return false;
        }

        // If excluding an ID (for updates), check if it's different
        if ($excludeId && $user->id === $excludeId) {
            return false;
        }

        return true;
    }
}
