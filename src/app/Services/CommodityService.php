<?php

namespace App\Services;

use App\Repositories\Contracts\CommodityRepositoryInterface;
use App\Repositories\Contracts\CommodityGradeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CommodityService
{
    protected CommodityRepositoryInterface $commodityRepository;
    protected CommodityGradeRepositoryInterface $gradeRepository;

    /**
     * CommodityService constructor.
     *
     * @param CommodityRepositoryInterface $commodityRepository
     * @param CommodityGradeRepositoryInterface $gradeRepository
     */
    public function __construct(
        CommodityRepositoryInterface $commodityRepository,
        CommodityGradeRepositoryInterface $gradeRepository
    ) {
        $this->commodityRepository = $commodityRepository;
        $this->gradeRepository = $gradeRepository;
    }

    /**
     * Get all commodities.
     *
     * @return Collection
     */
    public function getAllCommodities(): Collection
    {
        return $this->commodityRepository->all();
    }

    /**
     * Get all commodities with their grades.
     *
     * @return Collection
     */
    public function getAllCommoditiesWithGrades(): Collection
    {
        return $this->commodityRepository->getAllWithGrades();
    }

    /**
     * Get commodity by ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function getCommodityById(int $id): ?Model
    {
        return $this->commodityRepository->findById($id, ['*'], ['grades']);
    }

    /**
     * Create a new commodity.
     *
     * @param array $data
     * @return Model
     */
    public function createCommodity(array $data): Model
    {
        // Business logic: Validate unique name
        if ($this->commodityRepository->findByName($data['name'])) {
            throw new \Exception('Commodity with this name already exists');
        }

        // Business logic: Set default unit if not provided
        if (!isset($data['unit'])) {
            $data['unit'] = 'kg';
        }

        return $this->commodityRepository->create($data);
    }

    /**
     * Update commodity.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCommodity(int $id, array $data): bool
    {
        // Business logic: Validate unique name for other commodities
        if (isset($data['name'])) {
            $existing = $this->commodityRepository->findByName($data['name']);
            if ($existing && $existing->id !== $id) {
                throw new \Exception('Commodity with this name already exists');
            }
        }

        return $this->commodityRepository->update($id, $data);
    }

    /**
     * Delete commodity.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCommodity(int $id): bool
    {
        // Business logic: Check if commodity has grades
        $grades = $this->gradeRepository->getGradesByCommodity($id);
        if ($grades->isNotEmpty()) {
            throw new \Exception('Cannot delete commodity with existing grades. Delete grades first.');
        }

        return $this->commodityRepository->delete($id);
    }

    /**
     * Search commodities by name.
     *
     * @param string $name
     * @return Collection
     */
    public function searchCommodities(string $name): Collection
    {
        return $this->commodityRepository->searchByName($name);
    }

    // ==================== GRADE METHODS ====================

    /**
     * Get all grades for a commodity.
     *
     * @param int $commodityId
     * @return Collection
     */
    public function getGradesByCommodity(int $commodityId): Collection
    {
        return $this->gradeRepository->getGradesWithPrice($commodityId);
    }

    /**
     * Get grade by ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function getGradeById(int $id): ?Model
    {
        return $this->gradeRepository->findById($id, ['*'], ['commodity']);
    }

    /**
     * Create a new grade.
     *
     * @param array $data
     * @return Model
     */
    public function createGrade(array $data): Model
    {
        // Business logic: Validate commodity exists
        $commodity = $this->commodityRepository->findById($data['commodity_id']);
        if (!$commodity) {
            throw new \Exception('Commodity not found');
        }

        // Business logic: Validate unique grade name per commodity
        if ($this->gradeRepository->findByNameAndCommodity($data['commodity_id'], $data['grade_name'])) {
            throw new \Exception('Grade with this name already exists for this commodity');
        }

        // Business logic: Default price modifier is 0
        if (!isset($data['price_modifier'])) {
            $data['price_modifier'] = 0;
        }

        return $this->gradeRepository->create($data);
    }

    /**
     * Update grade.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateGrade(int $id, array $data): bool
    {
        $grade = $this->gradeRepository->findById($id);
        if (!$grade) {
            throw new \Exception('Grade not found');
        }

        // Business logic: Validate unique grade name if changed
        if (isset($data['grade_name'])) {
            $commodityId = $data['commodity_id'] ?? $grade->commodity_id;
            $existing = $this->gradeRepository->findByNameAndCommodity($commodityId, $data['grade_name']);
            if ($existing && $existing->id !== $id) {
                throw new \Exception('Grade with this name already exists for this commodity');
            }
        }

        return $this->gradeRepository->update($id, $data);
    }

    /**
     * Delete grade.
     *
     * @param int $id
     * @return bool
     */
    public function deleteGrade(int $id): bool
    {
        // Business logic: Can add check if grade is used in harvests/stocks
        // For now, allow deletion with cascade
        
        return $this->gradeRepository->delete($id);
    }

    /**
     * Get all grades with commodity info.
     *
     * @return Collection
     */
    public function getAllGrades(): Collection
    {
        return $this->gradeRepository->getAllWithCommodity();
    }
}
