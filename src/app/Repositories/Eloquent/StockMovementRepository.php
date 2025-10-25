<?php

namespace App\Repositories\Eloquent;

use App\Models\StockMovement;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class StockMovementRepository extends BaseRepository implements StockMovementRepositoryInterface
{
    /**
     * Specify Model class name
     */
    protected function makeModel(): Model
    {
        return app(StockMovement::class);
    }

    /**
     * Get movements by stock ID.
     */
    public function getByStock(int $stockId, int $limit = 50): Collection
    {
        return $this->model
            ->with(['creator:id,name'])
            ->where('stock_id', $stockId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get movements by type.
     */
    public function getByType(int $stockId, string $type): Collection
    {
        return $this->model
            ->with(['creator:id,name'])
            ->where('stock_id', $stockId)
            ->where('movement_type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get movements by date range.
     */
    public function getByDateRange(int $stockId, string $startDate, string $endDate): Collection
    {
        return $this->model
            ->with(['creator:id,name'])
            ->where('stock_id', $stockId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent movements for poktan.
     */
    public function getRecentByPoktan(int $poktanId, int $limit = 20): Collection
    {
        return $this->model
            ->with(['stock.commodity:id,name', 'stock.grade:id,grade_name', 'creator:id,name'])
            ->whereHas('stock', function ($query) use ($poktanId) {
                $query->where('poktan_id', $poktanId);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
