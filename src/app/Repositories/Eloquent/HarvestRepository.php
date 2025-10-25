<?php

namespace App\Repositories\Eloquent;

use App\Models\Harvest;
use App\Repositories\Contracts\HarvestRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HarvestRepository extends BaseRepository implements HarvestRepositoryInterface
{
    /**
     * Specify Model class name
     */
    protected function makeModel(): Model
    {
        return app(Harvest::class);
    }

    /**
     * Get harvests by poktan ID with relations.
     */
    public function getByPoktan(int $poktanId, array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->where('poktan_id', $poktanId)
            ->orderBy('harvest_date', 'desc')
            ->get();
    }

    /**
     * Get harvests by member ID.
     */
    public function getByMember(int $memberId, array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->where('member_id', $memberId)
            ->orderBy('harvest_date', 'desc')
            ->get();
    }

    /**
     * Get harvests by date range.
     */
    public function getByDateRange(string $startDate, string $endDate, ?int $poktanId = null): Collection
    {
        $query = $this->model
            ->with(['member', 'commodity', 'grade'])
            ->whereBetween('harvest_date', [$startDate, $endDate]);

        if ($poktanId) {
            $query->where('poktan_id', $poktanId);
        }

        return $query->orderBy('harvest_date', 'desc')->get();
    }

    /**
     * Get harvests by status.
     */
    public function getByStatus(string $status, ?int $poktanId = null): Collection
    {
        $query = $this->model
            ->with(['member', 'commodity', 'grade'])
            ->where('status', $status);

        if ($poktanId) {
            $query->where('poktan_id', $poktanId);
        }

        return $query->orderBy('harvest_date', 'desc')->get();
    }

    /**
     * Get harvests with full details (all relations).
     */
    public function getWithDetails(int $id): ?object
    {
        return $this->model
            ->with(['member', 'poktan', 'commodity', 'grade'])
            ->find($id);
    }

    /**
     * Get harvest summary by poktan.
     */
    public function getSummaryByPoktan(int $poktanId): array
    {
        $summary = $this->model
            ->where('poktan_id', $poktanId)
            ->select(
                'status',
                DB::raw('COUNT(*) as total_harvests'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('status')
            ->get();

        $result = [
            'total_harvests' => 0,
            'total_quantity' => 0,
            'by_status' => [
                'stored' => ['count' => 0, 'quantity' => 0],
                'sold' => ['count' => 0, 'quantity' => 0],
                'damaged' => ['count' => 0, 'quantity' => 0],
            ]
        ];

        foreach ($summary as $item) {
            $result['total_harvests'] += $item->total_harvests;
            $result['total_quantity'] += $item->total_quantity;
            $result['by_status'][$item->status] = [
                'count' => $item->total_harvests,
                'quantity' => (float) $item->total_quantity
            ];
        }

        return $result;
    }

    /**
     * Get total quantity by commodity and grade.
     */
    public function getTotalQuantity(int $commodityId, int $gradeId, ?int $poktanId = null): float
    {
        $query = $this->model
            ->where('commodity_id', $commodityId)
            ->where('grade_id', $gradeId)
            ->where('status', 'stored'); // Only count stored harvests

        if ($poktanId) {
            $query->where('poktan_id', $poktanId);
        }

        return (float) $query->sum('quantity');
    }
}
