<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CommodityRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Search commodities by name.
     *
     * @param string $name
     * @return Collection
     */
    public function searchByName(string $name): Collection;

    /**
     * Get commodities with their grades.
     *
     * @return Collection
     */
    public function getAllWithGrades(): Collection;

    /**
     * Get active commodities (not soft deleted).
     *
     * @return Collection
     */
    public function getActiveCommodities(): Collection;

    /**
     * Find commodity by name.
     *
     * @param string $name
     * @return mixed
     */
    public function findByName(string $name);
}
