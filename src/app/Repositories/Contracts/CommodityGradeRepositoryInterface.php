<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CommodityGradeRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get grades by commodity ID.
     *
     * @param int $commodityId
     * @return Collection
     */
    public function getGradesByCommodity(int $commodityId): Collection;

    /**
     * Find grade by name and commodity.
     *
     * @param int $commodityId
     * @param string $gradeName
     * @return mixed
     */
    public function findByNameAndCommodity(int $commodityId, string $gradeName);

    /**
     * Get all grades with commodity relationship.
     *
     * @return Collection
     */
    public function getAllWithCommodity(): Collection;

    /**
     * Get grades with price calculation.
     *
     * @param int $commodityId
     * @return Collection
     */
    public function getGradesWithPrice(int $commodityId): Collection;
}
