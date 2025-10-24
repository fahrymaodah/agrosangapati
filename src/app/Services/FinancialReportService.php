<?php

namespace App\Services;

use App\Repositories\FinancialReportRepository;
use App\Models\Poktan;
use Illuminate\Support\Facades\Log;

class FinancialReportService
{
    protected FinancialReportRepository $repository;

    public function __construct(FinancialReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate income statement report.
     */
    public function generateIncomeStatement(int $poktanId, string $startDate, string $endDate): array
    {
        try {
            $poktan = Poktan::find($poktanId);

            if (!$poktan) {
                return [
                    'success' => false,
                    'message' => 'Poktan not found',
                ];
            }

            $data = $this->repository->getIncomeStatement($poktanId, $startDate, $endDate);

            return [
                'success' => true,
                'data' => [
                    'poktan' => [
                        'id' => $poktan->id,
                        'name' => $poktan->nama,
                    ],
                    'report_type' => 'Income Statement',
                    'period' => $data['period'],
                    'income' => [
                        'categories' => $data['income']['by_category']->map(function ($item) {
                            return [
                                'category' => $item['category_name'],
                                'amount' => $item['total'],
                                'formatted_amount' => 'Rp ' . number_format($item['total'], 0, ',', '.'),
                                'count' => $item['count'],
                            ];
                        }),
                        'total' => $data['income']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['income']['total'], 0, ',', '.'),
                        'count' => $data['income']['count'],
                    ],
                    'expense' => [
                        'categories' => $data['expense']['by_category']->map(function ($item) {
                            return [
                                'category' => $item['category_name'],
                                'amount' => $item['total'],
                                'formatted_amount' => 'Rp ' . number_format($item['total'], 0, ',', '.'),
                                'count' => $item['count'],
                            ];
                        }),
                        'total' => $data['expense']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['expense']['total'], 0, ',', '.'),
                        'count' => $data['expense']['count'],
                    ],
                    'net_income' => $data['net_income'],
                    'formatted_net_income' => 'Rp ' . number_format($data['net_income'], 0, ',', '.'),
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate income statement: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate income statement: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate cash flow report.
     */
    public function generateCashFlowReport(int $poktanId, string $startDate, string $endDate): array
    {
        try {
            $poktan = Poktan::find($poktanId);

            if (!$poktan) {
                return [
                    'success' => false,
                    'message' => 'Poktan not found',
                ];
            }

            $data = $this->repository->getCashFlowReport($poktanId, $startDate, $endDate);

            return [
                'success' => true,
                'data' => [
                    'poktan' => [
                        'id' => $poktan->id,
                        'name' => $poktan->nama,
                    ],
                    'report_type' => 'Cash Flow Statement',
                    'period' => $data['period'],
                    'opening_balance' => $data['opening_balance'],
                    'formatted_opening_balance' => 'Rp ' . number_format($data['opening_balance'], 0, ',', '.'),
                    'cash_inflows' => [
                        'items' => $data['cash_inflows']['items']->map(function ($item) {
                            return [
                                'date' => $item['date'],
                                'description' => $item['description'],
                                'category' => $item['category'],
                                'amount' => $item['amount'],
                                'formatted_amount' => 'Rp ' . number_format($item['amount'], 0, ',', '.'),
                            ];
                        }),
                        'total' => $data['cash_inflows']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['cash_inflows']['total'], 0, ',', '.'),
                        'count' => $data['cash_inflows']['count'],
                    ],
                    'cash_outflows' => [
                        'items' => $data['cash_outflows']['items']->map(function ($item) {
                            return [
                                'date' => $item['date'],
                                'description' => $item['description'],
                                'category' => $item['category'],
                                'amount' => $item['amount'],
                                'formatted_amount' => 'Rp ' . number_format($item['amount'], 0, ',', '.'),
                            ];
                        }),
                        'total' => $data['cash_outflows']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['cash_outflows']['total'], 0, ',', '.'),
                        'count' => $data['cash_outflows']['count'],
                    ],
                    'net_cash_flow' => $data['net_cash_flow'],
                    'formatted_net_cash_flow' => 'Rp ' . number_format($data['net_cash_flow'], 0, ',', '.'),
                    'closing_balance' => $data['closing_balance'],
                    'formatted_closing_balance' => 'Rp ' . number_format($data['closing_balance'], 0, ',', '.'),
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate cash flow report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate cash flow report: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate balance sheet.
     */
    public function generateBalanceSheet(int $poktanId, string $asOfDate): array
    {
        try {
            $poktan = Poktan::find($poktanId);

            if (!$poktan) {
                return [
                    'success' => false,
                    'message' => 'Poktan not found',
                ];
            }

            $data = $this->repository->getBalanceSheet($poktanId, $asOfDate);

            return [
                'success' => true,
                'data' => [
                    'poktan' => [
                        'id' => $poktan->id,
                        'name' => $poktan->nama,
                    ],
                    'report_type' => 'Balance Sheet',
                    'as_of_date' => $data['as_of_date'],
                    'assets' => [
                        'cash' => $data['assets']['cash'],
                        'formatted_cash' => 'Rp ' . number_format($data['assets']['cash'], 0, ',', '.'),
                        'total' => $data['assets']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['assets']['total'], 0, ',', '.'),
                    ],
                    'equity' => [
                        'initial_capital' => $data['equity']['initial_capital'],
                        'formatted_initial_capital' => 'Rp ' . number_format($data['equity']['initial_capital'], 0, ',', '.'),
                        'retained_earnings' => $data['equity']['retained_earnings'],
                        'formatted_retained_earnings' => 'Rp ' . number_format($data['equity']['retained_earnings'], 0, ',', '.'),
                        'total' => $data['equity']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['equity']['total'], 0, ',', '.'),
                    ],
                    'summary' => [
                        'total_income' => $data['summary']['total_income'],
                        'formatted_total_income' => 'Rp ' . number_format($data['summary']['total_income'], 0, ',', '.'),
                        'total_expense' => $data['summary']['total_expense'],
                        'formatted_total_expense' => 'Rp ' . number_format($data['summary']['total_expense'], 0, ',', '.'),
                        'current_balance' => $data['summary']['current_balance'],
                        'formatted_current_balance' => 'Rp ' . number_format($data['summary']['current_balance'], 0, ',', '.'),
                    ],
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate balance sheet: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate balance sheet: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate transaction summary report.
     */
    public function generateTransactionSummary(int $poktanId, string $startDate, string $endDate, array $filters = []): array
    {
        try {
            $poktan = Poktan::find($poktanId);

            if (!$poktan) {
                return [
                    'success' => false,
                    'message' => 'Poktan not found',
                ];
            }

            $data = $this->repository->getTransactionSummary($poktanId, $startDate, $endDate, $filters);

            return [
                'success' => true,
                'data' => [
                    'poktan' => [
                        'id' => $poktan->id,
                        'name' => $poktan->nama,
                    ],
                    'report_type' => 'Transaction Summary',
                    'period' => $data['period'],
                    'statistics' => [
                        'total_transactions' => $data['statistics']['total_transactions'],
                        'approved_count' => $data['statistics']['approved_count'],
                        'pending_count' => $data['statistics']['pending_count'],
                        'rejected_count' => $data['statistics']['rejected_count'],
                        'income_count' => $data['statistics']['income_count'],
                        'expense_count' => $data['statistics']['expense_count'],
                        'total_income' => $data['statistics']['total_income'],
                        'formatted_total_income' => 'Rp ' . number_format($data['statistics']['total_income'], 0, ',', '.'),
                        'total_expense' => $data['statistics']['total_expense'],
                        'formatted_total_expense' => 'Rp ' . number_format($data['statistics']['total_expense'], 0, ',', '.'),
                        'net_amount' => $data['statistics']['net_amount'],
                        'formatted_net_amount' => 'Rp ' . number_format($data['statistics']['net_amount'], 0, ',', '.'),
                    ],
                    'transactions' => $data['transactions']->map(function ($trx) {
                        return [
                            'id' => $trx['id'],
                            'date' => $trx['date'],
                            'type' => ucfirst($trx['type']),
                            'category' => $trx['category'],
                            'description' => $trx['description'],
                            'amount' => $trx['amount'],
                            'formatted_amount' => 'Rp ' . number_format($trx['amount'], 0, ',', '.'),
                            'status' => ucfirst($trx['status']),
                            'created_by' => $trx['created_by'],
                            'approved_by' => $trx['approved_by'],
                        ];
                    })->values()->all(),
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate transaction summary: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate transaction summary: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate monthly detailed report.
     */
    public function generateMonthlyDetailedReport(int $poktanId, int $year, int $month): array
    {
        try {
            $poktan = Poktan::find($poktanId);

            if (!$poktan) {
                return [
                    'success' => false,
                    'message' => 'Poktan not found',
                ];
            }

            $data = $this->repository->getMonthlyDetailedReport($poktanId, $year, $month);

            return [
                'success' => true,
                'data' => [
                    'poktan' => [
                        'id' => $poktan->id,
                        'name' => $poktan->nama,
                    ],
                    'report_type' => 'Monthly Detailed Report',
                    'period' => $data['period'],
                    'opening_balance' => $data['opening_balance'],
                    'formatted_opening_balance' => 'Rp ' . number_format($data['opening_balance'], 0, ',', '.'),
                    'closing_balance' => $data['closing_balance'],
                    'formatted_closing_balance' => 'Rp ' . number_format($data['closing_balance'], 0, ',', '.'),
                    'total_income' => $data['total_income'],
                    'formatted_total_income' => 'Rp ' . number_format($data['total_income'], 0, ',', '.'),
                    'total_expense' => $data['total_expense'],
                    'formatted_total_expense' => 'Rp ' . number_format($data['total_expense'], 0, ',', '.'),
                    'net_change' => $data['net_change'],
                    'formatted_net_change' => 'Rp ' . number_format($data['net_change'], 0, ',', '.'),
                    'transaction_count' => $data['transaction_count'],
                    'daily_breakdown' => $data['daily_breakdown']->map(function ($day) {
                        return [
                            'date' => $day['date'],
                            'income' => $day['income'],
                            'formatted_income' => 'Rp ' . number_format($day['income'], 0, ',', '.'),
                            'expense' => $day['expense'],
                            'formatted_expense' => 'Rp ' . number_format($day['expense'], 0, ',', '.'),
                            'net' => $day['net'],
                            'formatted_net' => 'Rp ' . number_format($day['net'], 0, ',', '.'),
                            'transaction_count' => $day['transaction_count'],
                        ];
                    })->values()->all(),
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate monthly detailed report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate monthly detailed report: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate comparative report.
     */
    public function generateComparativeReport(int $poktanId, string $period1Start, string $period1End, string $period2Start, string $period2End): array
    {
        try {
            $poktan = Poktan::find($poktanId);

            if (!$poktan) {
                return [
                    'success' => false,
                    'message' => 'Poktan not found',
                ];
            }

            $data = $this->repository->getComparativeReport($poktanId, $period1Start, $period1End, $period2Start, $period2End);

            return [
                'success' => true,
                'data' => [
                    'poktan' => [
                        'id' => $poktan->id,
                        'name' => $poktan->nama,
                    ],
                    'report_type' => 'Comparative Report',
                    'period_1' => [
                        'range' => $data['period_1']['range'],
                        'total_income' => $data['period_1']['total_income'],
                        'formatted_total_income' => 'Rp ' . number_format($data['period_1']['total_income'], 0, ',', '.'),
                        'total_expense' => $data['period_1']['total_expense'],
                        'formatted_total_expense' => 'Rp ' . number_format($data['period_1']['total_expense'], 0, ',', '.'),
                        'net_income' => $data['period_1']['net_income'],
                        'formatted_net_income' => 'Rp ' . number_format($data['period_1']['net_income'], 0, ',', '.'),
                    ],
                    'period_2' => [
                        'range' => $data['period_2']['range'],
                        'total_income' => $data['period_2']['total_income'],
                        'formatted_total_income' => 'Rp ' . number_format($data['period_2']['total_income'], 0, ',', '.'),
                        'total_expense' => $data['period_2']['total_expense'],
                        'formatted_total_expense' => 'Rp ' . number_format($data['period_2']['total_expense'], 0, ',', '.'),
                        'net_income' => $data['period_2']['net_income'],
                        'formatted_net_income' => 'Rp ' . number_format($data['period_2']['net_income'], 0, ',', '.'),
                    ],
                    'variance' => [
                        'income' => $data['variance']['income'],
                        'formatted_income' => 'Rp ' . number_format($data['variance']['income'], 0, ',', '.'),
                        'expense' => $data['variance']['expense'],
                        'formatted_expense' => 'Rp ' . number_format($data['variance']['expense'], 0, ',', '.'),
                        'net_income' => $data['variance']['net_income'],
                        'formatted_net_income' => 'Rp ' . number_format($data['variance']['net_income'], 0, ',', '.'),
                    ],
                    'percentage_change' => [
                        'income' => round($data['percentage_change']['income'], 2) . '%',
                        'expense' => round($data['percentage_change']['expense'], 2) . '%',
                        'net_income' => round($data['percentage_change']['net_income'], 2) . '%',
                    ],
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate comparative report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate comparative report: ' . $e->getMessage(),
            ];
        }
    }
}
