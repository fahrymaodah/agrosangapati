<?php

namespace App\Repositories\Eloquent;

use App\Models\Harvest;
use App\Repositories\Contracts\ProductionReportRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductionReportRepository implements ProductionReportRepositoryInterface
{
    protected Harvest $model;

    public function __construct(Harvest $model)
    {
        $this->model = $model;
    }

        /**
     * Get harvest history for a member with date range filtering
     */
    public function getMemberHarvestHistory(
        int $memberId,
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $query = Harvest::with(['commodity:id,name,unit', 'grade:id,grade_name', 'poktan:id,name'])
            ->where('member_id', $memberId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('harvest_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('harvest_date', '<=', $endDate);
        }

        return $query->orderBy('harvest_date', 'desc')->get();
    }

    /**
     * Get member's production summary.
     */
    public function getMemberProductionSummary(int $memberId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = $this->model->where('member_id', $memberId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        $summary = $query->selectRaw('
            COUNT(DISTINCT commodity_id) as total_commodities,
            COUNT(*) as total_harvests,
            SUM(quantity) as total_quantity,
            MIN(harvest_date) as first_harvest,
            MAX(harvest_date) as last_harvest
        ')->first();

        return [
            'total_commodities' => $summary->total_commodities ?? 0,
            'total_harvests' => $summary->total_harvests ?? 0,
            'total_quantity' => $summary->total_quantity ?? 0,
            'first_harvest' => $summary->first_harvest,
            'last_harvest' => $summary->last_harvest,
        ];
    }

    /**
     * Get member's production by commodity.
     */
    public function getMemberProductionByCommodity(int $memberId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->select(
                'commodity_id',
                'commodities.name as commodity_name',
                'commodities.unit',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(*) as harvest_count'),
                DB::raw('AVG(quantity) as avg_quantity'),
                DB::raw('MIN(harvest_date) as first_harvest'),
                DB::raw('MAX(harvest_date) as last_harvest')
            )
            ->join('commodities', 'harvests.commodity_id', '=', 'commodities.id')
            ->where('harvests.member_id', $memberId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        return $query
            ->groupBy('commodity_id', 'commodities.name', 'commodities.unit')
            ->orderBy('total_quantity', 'desc')
            ->get();
    }

    /**
     * Get member's production comparison between two periods.
     */
    public function getMemberProductionComparison(int $memberId, string $currentStart, string $currentEnd, string $previousStart, string $previousEnd): array
    {
        // Current period
        $current = $this->model
            ->where('member_id', $memberId)
            ->whereBetween('harvest_date', [$currentStart, $currentEnd])
            ->selectRaw('
                COUNT(*) as harvest_count,
                SUM(quantity) as total_quantity,
                COUNT(DISTINCT commodity_id) as commodity_count
            ')
            ->first();

        // Previous period
        $previous = $this->model
            ->where('member_id', $memberId)
            ->whereBetween('harvest_date', [$previousStart, $previousEnd])
            ->selectRaw('
                COUNT(*) as harvest_count,
                SUM(quantity) as total_quantity,
                COUNT(DISTINCT commodity_id) as commodity_count
            ')
            ->first();

        $currentQuantity = $current->total_quantity ?? 0;
        $previousQuantity = $previous->total_quantity ?? 0;

        // Calculate percentage change
        $quantityChange = 0;
        if ($previousQuantity > 0) {
            $quantityChange = (($currentQuantity - $previousQuantity) / $previousQuantity) * 100;
        }

        return [
            'current_period' => [
                'start_date' => $currentStart,
                'end_date' => $currentEnd,
                'harvest_count' => $current->harvest_count ?? 0,
                'total_quantity' => $currentQuantity,
                'commodity_count' => $current->commodity_count ?? 0,
            ],
            'previous_period' => [
                'start_date' => $previousStart,
                'end_date' => $previousEnd,
                'harvest_count' => $previous->harvest_count ?? 0,
                'total_quantity' => $previousQuantity,
                'commodity_count' => $previous->commodity_count ?? 0,
            ],
            'comparison' => [
                'quantity_change' => round($quantityChange, 2),
                'quantity_diff' => $currentQuantity - $previousQuantity,
                'harvest_count_diff' => ($current->harvest_count ?? 0) - ($previous->harvest_count ?? 0),
                'trend' => $quantityChange > 0 ? 'up' : ($quantityChange < 0 ? 'down' : 'stable'),
            ],
        ];
    }

    /**
     * Get top producing members in a poktan.
     */
    public function getTopProducers(int $poktanId, int $limit = 10, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->select(
                'member_id',
                'users.name as member_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(*) as harvest_count'),
                DB::raw('COUNT(DISTINCT commodity_id) as commodity_count')
            )
            ->join('users', 'harvests.member_id', '=', 'users.id')
            ->where('harvests.poktan_id', $poktanId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        return $query
            ->groupBy('member_id', 'users.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get poktan's production summary.
     */
    public function getPoktanProductionSummary(int $poktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = $this->model->where('poktan_id', $poktanId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        $summary = $query->selectRaw('
            COUNT(DISTINCT commodity_id) as total_commodities,
            COUNT(DISTINCT member_id) as total_members,
            COUNT(*) as total_harvests,
            SUM(quantity) as total_quantity,
            MIN(harvest_date) as first_harvest,
            MAX(harvest_date) as last_harvest
        ')->first();

        return [
            'total_commodities' => $summary->total_commodities ?? 0,
            'total_members' => $summary->total_members ?? 0,
            'total_harvests' => $summary->total_harvests ?? 0,
            'total_quantity' => $summary->total_quantity ?? 0,
            'first_harvest' => $summary->first_harvest,
            'last_harvest' => $summary->last_harvest,
        ];
    }

    /**
     * Get poktan's production by commodity.
     */
    public function getPoktanProductionByCommodity(int $poktanId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->select(
                'commodity_id',
                'commodities.name as commodity_name',
                'commodities.unit',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(*) as harvest_count'),
                DB::raw('COUNT(DISTINCT member_id) as member_count'),
                DB::raw('AVG(quantity) as avg_quantity'),
                DB::raw('MIN(harvest_date) as first_harvest'),
                DB::raw('MAX(harvest_date) as last_harvest')
            )
            ->join('commodities', 'harvests.commodity_id', '=', 'commodities.id')
            ->where('harvests.poktan_id', $poktanId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        return $query
            ->groupBy('commodity_id', 'commodities.name', 'commodities.unit')
            ->orderBy('total_quantity', 'desc')
            ->get();
    }

    /**
     * Get poktan's production breakdown by member.
     */
    public function getPoktanProductionByMember(int $poktanId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->select(
                'member_id',
                'users.name as member_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(*) as harvest_count'),
                DB::raw('COUNT(DISTINCT commodity_id) as commodity_count'),
                DB::raw('MIN(harvest_date) as first_harvest'),
                DB::raw('MAX(harvest_date) as last_harvest')
            )
            ->join('users', 'harvests.member_id', '=', 'users.id')
            ->where('harvests.poktan_id', $poktanId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        return $query
            ->groupBy('member_id', 'users.name')
            ->orderBy('total_quantity', 'desc')
            ->get();
    }

    /**
     * Get poktan's monthly production trend.
     */
    public function getPoktanMonthlyTrend(int $poktanId, int $months = 12): Collection
    {
        return $this->model
            ->select(
                DB::raw('DATE_FORMAT(harvest_date, "%Y-%m") as month'),
                DB::raw('YEAR(harvest_date) as year'),
                DB::raw('MONTH(harvest_date) as month_number'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(*) as harvest_count'),
                DB::raw('COUNT(DISTINCT member_id) as member_count'),
                DB::raw('COUNT(DISTINCT commodity_id) as commodity_count')
            )
            ->where('poktan_id', $poktanId)
            ->where('harvest_date', '>=', now()->subMonths($months)->startOfMonth())
            ->groupBy('year', 'month_number', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month_number', 'asc')
            ->get();
    }

    /**
     * Get gapoktan's production summary (all poktans).
     */
    public function getGapoktanProductionSummary(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = $this->model
            ->join('poktans', 'harvests.poktan_id', '=', 'poktans.id')
            ->where('poktans.gapoktan_id', $gapoktanId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        $summary = $query->selectRaw('
            COUNT(DISTINCT harvests.commodity_id) as total_commodities,
            COUNT(DISTINCT harvests.poktan_id) as total_poktans,
            COUNT(DISTINCT harvests.member_id) as total_members,
            COUNT(*) as total_harvests,
            SUM(harvests.quantity) as total_quantity,
            MIN(harvest_date) as first_harvest,
            MAX(harvest_date) as last_harvest
        ')->first();

        return [
            'total_commodities' => $summary->total_commodities ?? 0,
            'total_poktans' => $summary->total_poktans ?? 0,
            'total_members' => $summary->total_members ?? 0,
            'total_harvests' => $summary->total_harvests ?? 0,
            'total_quantity' => $summary->total_quantity ?? 0,
            'first_harvest' => $summary->first_harvest,
            'last_harvest' => $summary->last_harvest,
        ];
    }

    /**
     * Get gapoktan's production by commodity.
     */
    public function getGapoktanProductionByCommodity(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->select(
                'harvests.commodity_id',
                'commodities.name as commodity_name',
                'commodities.unit',
                DB::raw('SUM(harvests.quantity) as total_quantity'),
                DB::raw('COUNT(*) as harvest_count'),
                DB::raw('COUNT(DISTINCT harvests.poktan_id) as poktan_count'),
                DB::raw('COUNT(DISTINCT harvests.member_id) as member_count'),
                DB::raw('AVG(harvests.quantity) as avg_quantity'),
                DB::raw('MIN(harvest_date) as first_harvest'),
                DB::raw('MAX(harvest_date) as last_harvest')
            )
            ->join('commodities', 'harvests.commodity_id', '=', 'commodities.id')
            ->join('poktans', 'harvests.poktan_id', '=', 'poktans.id')
            ->where('poktans.gapoktan_id', $gapoktanId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        return $query
            ->groupBy('harvests.commodity_id', 'commodities.name', 'commodities.unit')
            ->orderBy('total_quantity', 'desc')
            ->get();
    }

    /**
     * Get gapoktan's production breakdown by poktan.
     */
    public function getGapoktanProductionByPoktan(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->select(
                'harvests.poktan_id',
                'poktans.name as poktan_name',
                DB::raw('SUM(harvests.quantity) as total_quantity'),
                DB::raw('COUNT(*) as harvest_count'),
                DB::raw('COUNT(DISTINCT harvests.member_id) as member_count'),
                DB::raw('COUNT(DISTINCT harvests.commodity_id) as commodity_count'),
                DB::raw('MIN(harvest_date) as first_harvest'),
                DB::raw('MAX(harvest_date) as last_harvest')
            )
            ->join('poktans', 'harvests.poktan_id', '=', 'poktans.id')
            ->where('poktans.gapoktan_id', $gapoktanId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        return $query
            ->groupBy('harvests.poktan_id', 'poktans.name')
            ->orderBy('total_quantity', 'desc')
            ->get();
    }

    /**
     * Get gapoktan's poktan comparison/ranking.
     */
    public function getGapoktanPoktanComparison(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        // Get total for percentage calculation
        $total = $this->model
            ->join('poktans', 'harvests.poktan_id', '=', 'poktans.id')
            ->where('poktans.gapoktan_id', $gapoktanId);

        if ($startDate && $endDate) {
            $total->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        $totalQuantity = $total->sum('harvests.quantity');

        $query = $this->model
            ->select(
                'harvests.poktan_id',
                'poktans.name as poktan_name',
                DB::raw('SUM(harvests.quantity) as total_quantity'),
                DB::raw('COUNT(*) as harvest_count'),
                DB::raw('COUNT(DISTINCT harvests.member_id) as member_count'),
                DB::raw('COUNT(DISTINCT harvests.commodity_id) as commodity_count'),
                DB::raw('AVG(harvests.quantity) as avg_per_harvest')
            )
            ->join('poktans', 'harvests.poktan_id', '=', 'poktans.id')
            ->where('poktans.gapoktan_id', $gapoktanId);

        if ($startDate && $endDate) {
            $query->whereBetween('harvest_date', [$startDate, $endDate]);
        }

        $results = $query
            ->groupBy('harvests.poktan_id', 'poktans.name')
            ->orderBy('total_quantity', 'desc')
            ->get();

        // Add percentage and rank
        $rank = 1;
        return $results->map(function ($item) use (&$rank, $totalQuantity) {
            $item->rank = $rank++;
            $item->percentage = $totalQuantity > 0 ? round(($item->total_quantity / $totalQuantity) * 100, 2) : 0;
            return $item;
        });
    }

    /**
     * Get gapoktan's monthly production trend.
     */
    public function getGapoktanMonthlyTrend(int $gapoktanId, int $months = 12): Collection
    {
        return $this->model
            ->select(
                DB::raw('DATE_FORMAT(harvest_date, "%Y-%m") as month'),
                DB::raw('YEAR(harvest_date) as year'),
                DB::raw('MONTH(harvest_date) as month_number'),
                DB::raw('SUM(harvests.quantity) as total_quantity'),
                DB::raw('COUNT(*) as harvest_count'),
                DB::raw('COUNT(DISTINCT harvests.poktan_id) as poktan_count'),
                DB::raw('COUNT(DISTINCT harvests.member_id) as member_count'),
                DB::raw('COUNT(DISTINCT harvests.commodity_id) as commodity_count')
            )
            ->join('poktans', 'harvests.poktan_id', '=', 'poktans.id')
            ->where('poktans.gapoktan_id', $gapoktanId)
            ->where('harvest_date', '>=', now()->subMonths($months)->startOfMonth())
            ->groupBy('year', 'month_number', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month_number', 'asc')
            ->get();
    }
}
