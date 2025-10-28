<?php

namespace App\Services;

use App\Repositories\Contracts\HarvestRepositoryInterface;
use App\Repositories\Contracts\CommodityRepositoryInterface;
use App\Repositories\Contracts\CommodityGradeRepositoryInterface;
use App\Services\Contracts\FileUploadServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Exception;

class HarvestService
{
    protected HarvestRepositoryInterface $harvestRepository;
    protected CommodityRepositoryInterface $commodityRepository;
    protected CommodityGradeRepositoryInterface $gradeRepository;
    protected FileUploadServiceInterface $fileUploadService;

    public function __construct(
        HarvestRepositoryInterface $harvestRepository,
        CommodityRepositoryInterface $commodityRepository,
        CommodityGradeRepositoryInterface $gradeRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        $this->harvestRepository = $harvestRepository;
        $this->commodityRepository = $commodityRepository;
        $this->gradeRepository = $gradeRepository;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Get all harvests for a poktan.
     */
    public function getAllByPoktan(int $poktanId): array
    {
        $harvests = $this->harvestRepository->getByPoktan($poktanId, [
            'member:id,name',
            'commodity:id,name,unit',
            'grade:id,grade_name'
        ]);

        return $harvests->map(function ($harvest) {
            return $this->formatHarvestResponse($harvest);
        })->toArray();
    }

    /**
     * Get harvests by member.
     */
    public function getByMember(int $memberId): array
    {
        $harvests = $this->harvestRepository->getByMember($memberId, [
            'poktan:id,name',
            'commodity:id,name,unit',
            'grade:id,grade_name'
        ]);

        return $harvests->map(function ($harvest) {
            return $this->formatHarvestResponse($harvest);
        })->toArray();
    }

    /**
     * Get harvest by ID with details.
     */
    public function getById(int $id): ?array
    {
        $harvest = $this->harvestRepository->getWithDetails($id);

        if (!$harvest) {
            return null;
        }

        return $this->formatHarvestResponse($harvest);
    }

    /**
     * Get harvests by date range.
     */
    public function getByDateRange(string $startDate, string $endDate, ?int $poktanId = null): array
    {
        $harvests = $this->harvestRepository->getByDateRange($startDate, $endDate, $poktanId);

        return $harvests->map(function ($harvest) {
            return $this->formatHarvestResponse($harvest);
        })->toArray();
    }

    /**
     * Get harvests by status.
     */
    public function getByStatus(string $status, ?int $poktanId = null): array
    {
        // Validate status
        if (!in_array($status, ['stored', 'sold', 'damaged'])) {
            throw new Exception("Invalid status. Must be: stored, sold, or damaged");
        }

        $harvests = $this->harvestRepository->getByStatus($status, $poktanId);

        return $harvests->map(function ($harvest) {
            return $this->formatHarvestResponse($harvest);
        })->toArray();
    }

    /**
     * Create new harvest.
     */
    public function create(array $data): array
    {
        // Validate commodity exists
        $commodity = $this->commodityRepository->findById($data['commodity_id']);
        if (!$commodity) {
            throw new Exception("Commodity not found");
        }

        // Validate grade exists and belongs to commodity
        $grade = $this->gradeRepository->findById($data['grade_id']);
        if (!$grade || $grade->commodity_id != $data['commodity_id']) {
            throw new Exception("Grade not found or does not belong to the selected commodity");
        }

        // Set unit from commodity if not provided
        if (empty($data['unit'])) {
            $data['unit'] = $commodity->unit;
        }

        // Handle photo upload
        if (isset($data['harvest_photo']) && $data['harvest_photo'] instanceof UploadedFile) {
            $data['harvest_photo'] = $this->storePhoto($data['harvest_photo']);
        }

        // Set default status
        if (empty($data['status'])) {
            $data['status'] = 'stored';
        }

        $harvest = $this->harvestRepository->create($data);
        
        return $this->formatHarvestResponse(
            $this->harvestRepository->getWithDetails($harvest->id)
        );
    }

    /**
     * Update harvest.
     */
    public function update(int $id, array $data): ?array
    {
        $harvest = $this->harvestRepository->findById($id);

        if (!$harvest) {
            return null;
        }

        // If commodity_id changed, validate it
        if (isset($data['commodity_id']) && $data['commodity_id'] != $harvest->commodity_id) {
            $commodity = $this->commodityRepository->findById($data['commodity_id']);
            if (!$commodity) {
                throw new Exception("Commodity not found");
            }
        }

        // If grade_id changed, validate it
        if (isset($data['grade_id'])) {
            $commodityId = $data['commodity_id'] ?? $harvest->commodity_id;
            $grade = $this->gradeRepository->findById($data['grade_id']);
            
            if (!$grade || $grade->commodity_id != $commodityId) {
                throw new Exception("Grade not found or does not belong to the selected commodity");
            }
        }

        // Handle photo upload
        if (isset($data['harvest_photo']) && $data['harvest_photo'] instanceof UploadedFile) {
            // Delete old photo if exists using FileUploadService
            if ($harvest->harvest_photo) {
                $this->fileUploadService->deleteFile($harvest->harvest_photo);
            }
            $data['harvest_photo'] = $this->storePhoto($data['harvest_photo']);
        }

        $updated = $this->harvestRepository->update($id, $data);

        if (!$updated) {
            return null;
        }

        return $this->formatHarvestResponse(
            $this->harvestRepository->getWithDetails($id)
        );
    }

    /**
     * Delete harvest.
     */
    public function delete(int $id): bool
    {
        $harvest = $this->harvestRepository->findById($id);

        if (!$harvest) {
            return false;
        }

        // Delete photo if exists
        if ($harvest->harvest_photo) {
            Storage::disk('public')->delete($harvest->harvest_photo);
        }

        return $this->harvestRepository->delete($id);
    }

    /**
     * Update harvest status.
     */
    public function updateStatus(int $id, string $status): ?array
    {
        // Validate status
        if (!in_array($status, ['stored', 'sold', 'damaged'])) {
            throw new Exception("Invalid status. Must be: stored, sold, or damaged");
        }

        $updated = $this->harvestRepository->update($id, ['status' => $status]);

        if (!$updated) {
            return null;
        }

        return $this->formatHarvestResponse(
            $this->harvestRepository->getWithDetails($id)
        );
    }

    /**
     * Get harvest summary for poktan.
     */
    public function getSummary(int $poktanId): array
    {
        return $this->harvestRepository->getSummaryByPoktan($poktanId);
    }

    /**
     * Store harvest photo using FileUploadService with optimization.
     */
    protected function storePhoto(UploadedFile $file): string
    {
        $result = $this->fileUploadService->uploadImage($file, 'harvests', [
            'optimize' => true,
            'max_image_width' => 1600,
            'max_image_height' => 1200,
            'image_quality' => 85,
            'generate_thumbnail' => true,
            'thumbnail_size' => 400,
        ]);
        
        return $result['path'];
    }

    /**
     * Format harvest response.
     */
    protected function formatHarvestResponse($harvest): array
    {
        return [
            'id' => $harvest->id,
            'member' => [
                'id' => $harvest->member->id,
                'name' => $harvest->member->name,
            ],
            'poktan' => $harvest->poktan ? [
                'id' => $harvest->poktan->id,
                'name' => $harvest->poktan->name,
            ] : null,
            'commodity' => [
                'id' => $harvest->commodity->id,
                'name' => $harvest->commodity->name,
                'unit' => $harvest->commodity->unit,
            ],
            'grade' => [
                'id' => $harvest->grade->id,
                'name' => $harvest->grade->grade_name,
            ],
            'quantity' => (float) $harvest->quantity,
            'unit' => $harvest->unit,
            'harvest_date' => $harvest->harvest_date->format('Y-m-d'),
            'harvest_photo' => $harvest->harvest_photo ? Storage::url($harvest->harvest_photo) : null,
            'status' => $harvest->status,
            'notes' => $harvest->notes,
            'created_at' => $harvest->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $harvest->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
