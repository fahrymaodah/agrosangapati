<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * BaseRepository constructor
     */
    public function __construct()
    {
        $this->model = $this->makeModel();
    }

    /**
     * Make model instance
     *
     * @return Model
     */
    abstract protected function makeModel(): Model;

    /**
     * Get all records
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    /**
     * Find record by ID
     */
    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Find record by specific column
     */
    public function findByColumn(string $column, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($column, $value)->first($columns);
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->findById($id);
        
        if (!$record) {
            return false;
        }

        return $record->update($data);
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $record = $this->findById($id);
        
        if (!$record) {
            return false;
        }

        return $record->delete();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Find records by multiple conditions
     */
    public function findWhere(array $conditions, array $columns = ['*']): Collection
    {
        $query = $this->model->query();

        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        return $query->get($columns);
    }
}
