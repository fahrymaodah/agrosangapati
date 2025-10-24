<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     *
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Find record by ID
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Model|null
     */
    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Find record by specific column
     *
     * @param string $column
     * @param mixed $value
     * @param array $columns
     * @return Model|null
     */
    public function findByColumn(string $column, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Create new record
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get paginated records
     *
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']);

    /**
     * Find records by multiple conditions
     *
     * @param array $conditions
     * @param array $columns
     * @return Collection
     */
    public function findWhere(array $conditions, array $columns = ['*']): Collection;
}
