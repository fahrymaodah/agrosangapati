<?php

namespace App\Repositories\Eloquent;

use App\Models\Commodity;
use App\Repositories\Contracts\CommodityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CommodityRepository extends BaseRepository implements CommodityRepositoryInterface
{
    /**
     * Make model instance
     *
     * @return Model
     */
    protected function makeModel(): Model
    {
        return new Commodity();
    }

    /**
     * Search commodities by name.
     *
     * @param string $name
     * @return Collection
     */
    public function searchByName(string $name): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$name}%")
            ->get();
    }

    /**
     * Get commodities with their grades.
     *
     * @return Collection
     */
    public function getAllWithGrades(): Collection
    {
        return $this->model
            ->with('grades')
            ->get();
    }

    /**
     * Get active commodities (not soft deleted).
     *
     * @return Collection
     */
    public function getActiveCommodities(): Collection
    {
        return $this->model->get();
    }

    /**
     * Find commodity by name.
     *
     * @param string $name
     * @return mixed
     */
    public function findByName(string $name)
    {
        return $this->model
            ->where('name', $name)
            ->first();
    }
}
