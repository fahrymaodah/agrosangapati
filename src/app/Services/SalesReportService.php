<?php

namespace App\Services;

use App\Repositories\SalesReportRepository;

class SalesReportService
{
    protected $reportRepository;

    public function __construct(SalesReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    /**
     * Get sales report by product.
     */
    public function getSalesByProduct(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $productId = $filters['product_id'] ?? null;

        return $this->reportRepository->getSalesByProduct($startDate, $endDate, $productId);
    }

    /**
     * Get sales report by poktan.
     */
    public function getSalesByPoktan(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $poktanId = $filters['poktan_id'] ?? null;

        return $this->reportRepository->getSalesByPoktan($startDate, $endDate, $poktanId);
    }

    /**
     * Get best selling products.
     */
    public function getBestSellingProducts(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $limit = $filters['limit'] ?? 10;

        return $this->reportRepository->getBestSellingProducts($startDate, $endDate, $limit);
    }

    /**
     * Get revenue analysis with trends.
     */
    public function getRevenueAnalysis(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $groupBy = $filters['group_by'] ?? 'day'; // day, week, month, year

        return $this->reportRepository->getRevenueAnalysis($startDate, $endDate, $groupBy);
    }

    /**
     * Get comprehensive sales summary.
     */
    public function getSalesSummary(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();

        return $this->reportRepository->getSalesSummary($startDate, $endDate);
    }

    /**
     * Get top customers.
     */
    public function getTopCustomers(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $limit = $filters['limit'] ?? 10;

        return $this->reportRepository->getTopCustomers($startDate, $endDate, $limit);
    }

    /**
     * Get complete sales report (all data combined).
     */
    public function getCompleteSalesReport(array $filters = []): array
    {
        return [
            'summary' => $this->getSalesSummary($filters),
            'by_product' => $this->getSalesByProduct($filters),
            'by_poktan' => $this->getSalesByPoktan($filters),
            'best_selling' => $this->getBestSellingProducts($filters),
            'revenue_trend' => $this->getRevenueAnalysis($filters),
            'top_customers' => $this->getTopCustomers($filters),
        ];
    }
}
