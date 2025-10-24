<?php

namespace App\Services;

use App\Repositories\DashboardRepository;
use App\Models\Poktan;
use App\Models\Gapoktan;

class DashboardService
{
    protected $dashboardRepository;

    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Get complete dashboard data for Poktan
     */
    public function getPoktanDashboard(int $poktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Validate poktan exists
        $poktan = Poktan::find($poktanId);
        if (!$poktan) {
            return [
                'success' => false,
                'message' => 'Poktan tidak ditemukan',
            ];
        }

        // Get financial summary
        $summary = $this->dashboardRepository->getPoktanFinancialSummary($poktanId, $startDate, $endDate);

        // Get monthly trend (6 months)
        $trend = $this->dashboardRepository->getPoktanMonthlyTrend($poktanId, 6);

        // Get recent transactions
        $recentTransactions = $this->dashboardRepository->getRecentTransactions($poktanId, 10);

        // Get pending approvals
        $pendingApprovals = $this->dashboardRepository->getPendingApprovals($poktanId);

        // Get category breakdown
        $categoryBreakdown = $this->dashboardRepository->getCategoryBreakdown($poktanId, $startDate, $endDate);

        return [
            'success' => true,
            'data' => [
                'poktan' => [
                    'id' => $poktan->id,
                    'name' => $poktan->name,
                ],
                'summary' => [
                    'total_income' => $this->formatRupiah($summary['total_income']),
                    'total_expense' => $this->formatRupiah($summary['total_expense']),
                    'net_income' => $this->formatRupiah($summary['net_income']),
                    'current_balance' => $this->formatRupiah($summary['current_balance']),
                    'pending_transactions' => $summary['pending_transactions'],
                    'period' => $summary['period'],
                ],
                'monthly_trend' => array_map(function ($item) {
                    return [
                        'month' => $item['month'],
                        'month_num' => $item['month_num'],
                        'income' => $this->formatRupiah($item['income']),
                        'expense' => $this->formatRupiah($item['expense']),
                        'net' => $this->formatRupiah($item['net']),
                        'income_raw' => $item['income'],
                        'expense_raw' => $item['expense'],
                        'net_raw' => $item['net'],
                    ];
                }, $trend),
                'recent_transactions' => array_map(function ($transaction) {
                    return [
                        'id' => $transaction['id'],
                        'transaction_type' => $transaction['transaction_type'],
                        'amount' => $this->formatRupiah($transaction['amount']),
                        'amount_raw' => $transaction['amount'],
                        'description' => $transaction['description'],
                        'transaction_date' => $transaction['transaction_date'],
                        'status' => $transaction['status'],
                        'category' => $transaction['category']['name'] ?? null,
                        'created_by' => $transaction['creator']['name'] ?? null,
                    ];
                }, $recentTransactions),
                'pending_approvals' => array_map(function ($transaction) {
                    return [
                        'id' => $transaction['id'],
                        'transaction_type' => $transaction['transaction_type'],
                        'amount' => $this->formatRupiah($transaction['amount']),
                        'amount_raw' => $transaction['amount'],
                        'description' => $transaction['description'],
                        'transaction_date' => $transaction['transaction_date'],
                        'category' => $transaction['category']['name'] ?? null,
                        'created_by' => $transaction['creator']['name'] ?? null,
                        'created_at' => $transaction['created_at'],
                    ];
                }, $pendingApprovals),
                'category_breakdown' => $categoryBreakdown,
            ],
        ];
    }

    /**
     * Get complete dashboard data for Gapoktan
     */
    public function getGapoktanDashboard(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Validate gapoktan exists
        $gapoktan = Gapoktan::find($gapoktanId);
        if (!$gapoktan) {
            return [
                'success' => false,
                'message' => 'Gapoktan tidak ditemukan',
            ];
        }

        // Get financial summary
        $summary = $this->dashboardRepository->getGapoktanFinancialSummary($gapoktanId, $startDate, $endDate);

        // Get monthly trend (6 months)
        $trend = $this->dashboardRepository->getGapoktanMonthlyTrend($gapoktanId, 6);

        // Get recent transactions
        $recentTransactions = $this->dashboardRepository->getGapoktanRecentTransactions($gapoktanId, 10);

        // Get pending approvals
        $pendingApprovals = $this->dashboardRepository->getGapoktanPendingApprovals($gapoktanId);

        return [
            'success' => true,
            'data' => [
                'gapoktan' => [
                    'id' => $gapoktan->id,
                    'name' => $gapoktan->name,
                ],
                'summary' => [
                    'total_income' => $this->formatRupiah($summary['total_income']),
                    'total_expense' => $this->formatRupiah($summary['total_expense']),
                    'net_income' => $this->formatRupiah($summary['net_income']),
                    'total_balance' => $this->formatRupiah($summary['total_balance']),
                    'pending_transactions' => $summary['pending_transactions'],
                    'poktan_count' => $summary['poktan_count'],
                    'period' => $summary['period'],
                ],
                'monthly_trend' => array_map(function ($item) {
                    return [
                        'month' => $item['month'],
                        'month_num' => $item['month_num'],
                        'income' => $this->formatRupiah($item['income']),
                        'expense' => $this->formatRupiah($item['expense']),
                        'net' => $this->formatRupiah($item['net']),
                        'income_raw' => $item['income'],
                        'expense_raw' => $item['expense'],
                        'net_raw' => $item['net'],
                    ];
                }, $trend),
                'recent_transactions' => array_map(function ($transaction) {
                    return [
                        'id' => $transaction['id'],
                        'transaction_type' => $transaction['transaction_type'],
                        'amount' => $this->formatRupiah($transaction['amount']),
                        'amount_raw' => $transaction['amount'],
                        'description' => $transaction['description'],
                        'transaction_date' => $transaction['transaction_date'],
                        'status' => $transaction['status'],
                        'category' => $transaction['category']['name'] ?? null,
                        'poktan' => $transaction['poktan']['name'] ?? null,
                        'created_by' => $transaction['creator']['name'] ?? null,
                    ];
                }, $recentTransactions),
                'pending_approvals' => array_map(function ($transaction) {
                    return [
                        'id' => $transaction['id'],
                        'transaction_type' => $transaction['transaction_type'],
                        'amount' => $this->formatRupiah($transaction['amount']),
                        'amount_raw' => $transaction['amount'],
                        'description' => $transaction['description'],
                        'transaction_date' => $transaction['transaction_date'],
                        'category' => $transaction['category']['name'] ?? null,
                        'poktan' => $transaction['poktan']['name'] ?? null,
                        'created_by' => $transaction['creator']['name'] ?? null,
                        'created_at' => $transaction['created_at'],
                    ];
                }, $pendingApprovals),
            ],
        ];
    }

    /**
     * Format number to Rupiah
     */
    private function formatRupiah($amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
