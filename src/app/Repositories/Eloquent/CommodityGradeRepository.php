<?php

namespace App\Repositories\Eloquent;

use App\Models\CommodityGrade;
use App\Repositories\Contracts\CommodityGradeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CommodityGradeRepository extends BaseRepository implements CommodityGradeRepositoryInterface
{
    /**
     * Make model instance
     *
     * @return Model
     */
    protected function makeModel(): Model
    {
        return new CommodityGrade();
    }

    /**
     * Get grades by commodity ID.
     *
     * @param int $commodityId
     * @return Collection
     */
    public function getGradesByCommodity(int $commodityId): Collection
    {
        return $this->model
            ->where('commodity_id', $commodityId)
            ->get();
    }

    /**
     * Find grade by name and commodity.
     *
     * @param int $commodityId
     * @param string $gradeName
     * @return mixed
     */
    public function findByNameAndCommodity(int $commodityId, string $gradeName)
    {
        return $this->model
            ->where('commodity_id', $commodityId)
            ->where('grade_name', $gradeName)
            ->first();
    }

    /**
     * Get all grades with commodity relationship.
     *
     * @return Collection
     */
    public function getAllWithCommodity(): Collection
    {
        return $this->model
            ->with('commodity')
            ->get();
    }

    /**
     * Get grades with price calculation.
     *
     * @param int $commodityId
     * @return Collection
     */
    public function getGradesWithPrice(int $commodityId): Collection
    {
        return $this->model
            ->with('commodity')
            ->where('commodity_id', $commodityId)
            ->get()
            ->map(function ($grade) {
                $grade->actual_price = $grade->getActualPriceAttribute();
                return $grade;
            });
    }
}
