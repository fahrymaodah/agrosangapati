<?php

namespace App\Services;

use App\Repositories\Contracts\HarvestRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\ProductionReportRepositoryInterface;
use Carbon\Carbon;

class HarvestDashboardService
{
    protected $harvestRepository;
    protected $stockRepository;
    protected $productionRepository;

    public function __construct(
        HarvestRepositoryInterface $harvestRepository,
        StockRepositoryInterface $stockRepository,
        ProductionReportRepositoryInterface $productionRepository
    ) {
        $this->harvestRepository = $harvestRepository;
        $this->stockRepository = $stockRepository;
        $this->productionRepository = $productionRepository;
    }

    /**
     * Get poktan harvest dashboard data.
     */
    public function getPoktanDashboard(int $poktanId): array
    {
        // Summary cards
        $totalProduction = $this->getTotalProduction($poktanId);
        $totalStock = $this->getTotalStock($poktanId);
        
        // Recent harvests (last 5)
        $recentHarvests = $this->harvestRepository->getByPoktan($poktanId, ['commodity', 'member'])
            ->take(5)
            ->map(function ($harvest) {
                return [
                    'id' => $harvest->id,
                    'harvest_date' => $harvest->harvest_date,
                    'quantity' => (float) $harvest->quantity,
                    'commodity_name' => $harvest->commodity->name ?? null,
                    'member_name' => $harvest->member->name ?? null,
                ];
            });
        
        $lowStockItems = $this->stockRepository->getLowStock($poktanId)
            ->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'commodity_name' => $stock->commodity->name ?? null,
                    'grade_name' => $stock->grade->grade_name ?? null,
                    'quantity' => (float) $stock->quantity,
                    'location' => $stock->location,
                ];
            });

        // Production by commodity (for pie chart)
        $productionByCommodity = $this->productionRepository->getPoktanProductionByCommodity(
            $poktanId,
            Carbon::now()->subMonths(6)->startOfMonth()->toDateString(),
            Carbon::now()->endOfMonth()->toDateString()
        )->map(function ($item) {
            return [
                'commodity_name' => $item->commodity_name,
                'total_quantity' => (float) $item->total_quantity,
                'harvest_count' => $item->harvest_count,
            ];
        });

        // Monthly trend (for line chart)
        $monthlyTrend = $this->productionRepository->getPoktanMonthlyTrend($poktanId, 6)
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'total_quantity' => (float) $item->total_quantity,
                    'harvest_count' => $item->harvest_count,
                ];
            });

        // Top producers
        $topProducers = $this->productionRepository->getTopProducers($poktanId, 5)
            ->map(function ($item) {
                return [
                    'member_name' => $item->member_name,
                    'total_quantity' => (float) $item->total_quantity,
                    'harvest_count' => $item->harvest_count,
                ];
            });

        return [
            'summary' => [
                'total_production' => $totalProduction,
                'total_stock' => $totalStock,
                'low_stock_count' => count($lowStockItems),
                'recent_harvest_count' => count($recentHarvests),
            ],
            'production_by_commodity' => $productionByCommodity,
            'monthly_trend' => $monthlyTrend,
            'recent_harvests' => $recentHarvests,
            'low_stock_items' => $lowStockItems,
            'top_producers' => $topProducers,
        ];
    }

    /**
     * Get gapoktan harvest dashboard data.
     */
    public function getGapoktanDashboard(int $gapoktanId): array
    {
        // Summary cards
        $summary = $this->productionRepository->getGapoktanProductionSummary(
            $gapoktanId,
            Carbon::now()->startOfMonth()->toDateString(),
            Carbon::now()->endOfMonth()->toDateString()
        );

        $gapoktanStock = $this->stockRepository->getByPoktan(null); // poktan_id = null for gapoktan
        $totalGapoktanStock = $gapoktanStock->sum('quantity');

        // Production by commodity (consolidated)
        $productionByCommodity = $this->productionRepository->getGapoktanProductionByCommodity(
            $gapoktanId,
            Carbon::now()->subMonths(6)->startOfMonth()->toDateString(),
            Carbon::now()->endOfMonth()->toDateString()
        );

        // Monthly trend (consolidated)
        $monthlyTrend = $this->productionRepository->getGapoktanMonthlyTrend($gapoktanId, 6);

        // Poktan comparison/ranking
        $poktanComparison = $this->productionRepository->getGapoktanPoktanComparison(
            $gapoktanId,
            Carbon::now()->startOfMonth()->toDateString(),
            Carbon::now()->endOfMonth()->toDateString()
        );

        return [
            'summary' => [
                'total_production' => $summary['total_quantity'],
                'total_poktans' => $summary['total_poktans'],
                'total_members' => $summary['total_members'],
                'total_harvests' => $summary['total_harvests'],
                'total_commodities' => $summary['total_commodities'],
                'gapoktan_stock' => $totalGapoktanStock,
            ],
            'production_by_commodity' => $productionByCommodity,
            'monthly_trend' => $monthlyTrend,
            'poktan_comparison' => $poktanComparison,
        ];
    }

    /**
     * Get poktan dashboard cards only (quick summary).
     */
    public function getPoktanDashboardCards(int $poktanId): array
    {
        $totalProduction = $this->getTotalProduction($poktanId);
        $totalStock = $this->getTotalStock($poktanId);
        $lowStockItems = $this->stockRepository->getLowStock($poktanId);

        return [
            'total_production' => $totalProduction,
            'total_stock' => $totalStock,
            'low_stock_count' => count($lowStockItems),
        ];
    }

    /**
     * Get gapoktan dashboard cards only (quick summary).
     */
    public function getGapoktanDashboardCards(int $gapoktanId): array
    {
        $summary = $this->productionRepository->getGapoktanProductionSummary(
            $gapoktanId,
            Carbon::now()->startOfMonth()->toDateString(),
            Carbon::now()->endOfMonth()->toDateString()
        );

        $gapoktanStock = $this->stockRepository->getByPoktan(null);
        $totalGapoktanStock = $gapoktanStock->sum('quantity');

        return [
            'total_production' => $summary['total_quantity'],
            'total_poktans' => $summary['total_poktans'],
            'total_members' => $summary['total_members'],
            'gapoktan_stock' => $totalGapoktanStock,
        ];
    }

    /**
     * Get total production for poktan (current month).
     */
    private function getTotalProduction(int $poktanId): float
    {
        $summary = $this->productionRepository->getPoktanProductionSummary(
            $poktanId,
            Carbon::now()->startOfMonth()->toDateString(),
            Carbon::now()->endOfMonth()->toDateString()
        );

        return (float) $summary['total_quantity'];
    }

    /**
     * Get total stock for poktan.
     */
    private function getTotalStock(int $poktanId): float
    {
        $stocks = $this->stockRepository->getByPoktan($poktanId);
        return (float) $stocks->sum('quantity');
    }
}
