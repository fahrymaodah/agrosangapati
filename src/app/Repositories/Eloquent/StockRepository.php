<?php

namespace App\Repositories\Eloquent;

use App\Models\Stock;
use App\Repositories\Contracts\StockRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockRepository extends BaseRepository implements StockRepositoryInterface
{
    /**
     * Specify Model class name
     */
    protected function makeModel(): Model
    {
        return app(Stock::class);
    }

    /**
     * Get stock by poktan ID with relations.
     * If poktanId is null, get gapoktan stocks.
     */
    public function getByPoktan(?int $poktanId, array $relations = []): Collection
    {
        $query = $this->model->with($relations);
        
        if ($poktanId === null) {
            $query->whereNull('poktan_id');
        } else {
            $query->where('poktan_id', $poktanId);
        }
        
        return $query->orderBy('commodity_id')
            ->orderBy('grade_id')
            ->get();
    }

    /**
     * Get stock by commodity and grade.
     * If poktanId is null, get from gapoktan stocks.
     */
    public function getByCommodityGrade(?int $poktanId, int $commodityId, int $gradeId, ?string $location = null)
    {
        $query = $this->model
            ->where('commodity_id', $commodityId)
            ->where('grade_id', $gradeId);

        if ($poktanId === null) {
            $query->whereNull('poktan_id');
        } else {
            $query->where('poktan_id', $poktanId);
        }

        // Only filter by location if specifically provided
        if ($location !== null) {
            $query->where('location', $location);
        }

        return $query->first();
    }

    /**
     * Get low stock items.
     */
    public function getLowStock(int $poktanId, float $minimumQuantity = 100): Collection
    {
        return $this->model
            ->with(['poktan', 'commodity', 'grade'])
            ->where('poktan_id', $poktanId)
            ->where('quantity', '<', $minimumQuantity)
            ->orderBy('quantity', 'asc')
            ->get();
    }

    /**
     * Get stock by location.
     */
    public function getByLocation(int $poktanId, ?string $location): Collection
    {
        return $this->model
            ->with(['commodity', 'grade'])
            ->where('poktan_id', $poktanId)
            ->where('location', $location)
            ->orderBy('commodity_id')
            ->get();
    }

    /**
     * Get stock summary by poktan.
     * If poktanId is null, get gapoktan summary.
     */
    public function getSummaryByPoktan(?int $poktanId): array
    {
        $query = $this->model->select(
            DB::raw('COUNT(DISTINCT commodity_id) as total_commodities'),
            DB::raw('COUNT(stocks.id) as total_items'),
            DB::raw('SUM(quantity) as total_quantity')
        );
        
        if ($poktanId === null) {
            $query->whereNull('poktan_id');
        } else {
            $query->where('poktan_id', $poktanId);
        }
        
        $summary = $query->first();

        $commodityQuery = $this->model
            ->select(
                'commodity_id',
                'commodities.name as commodity_name',
                'commodities.unit',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(stocks.id) as grade_count')
            )
            ->join('commodities', 'stocks.commodity_id', '=', 'commodities.id');
            
        if ($poktanId === null) {
            $commodityQuery->whereNull('stocks.poktan_id');
        } else {
            $commodityQuery->where('stocks.poktan_id', $poktanId);
        }
        
        $byCommodity = $commodityQuery
            ->groupBy('commodity_id', 'commodities.name', 'commodities.unit')
            ->get();

        $lowStockQuery = $this->model
            ->select('stocks.id', 'commodity_id', 'grade_id', 'quantity', 'unit')
            ->with(['commodity:id,name', 'grade:id,grade_name']);
            
        if ($poktanId === null) {
            $lowStockQuery->whereNull('poktan_id');
        } else {
            $lowStockQuery->where('poktan_id', $poktanId);
        }
        
        $lowStock = $lowStockQuery
            ->where('quantity', '<', 100)
            ->orderBy('quantity', 'asc')
            ->limit(10)
            ->get();

        return [
            'total_commodities' => $summary->total_commodities ?? 0,
            'total_items' => $summary->total_items ?? 0,
            'total_quantity' => $summary->total_quantity ?? 0,
            'by_commodity' => $byCommodity,
            'low_stock_items' => $lowStock,
        ];
    }

    /**
     * Update or create stock.
     */
    public function updateOrCreateStock(array $conditions, array $data)
    {
        return $this->model->updateOrCreate($conditions, $data);
    }
}
