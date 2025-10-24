<?php

namespace App\Services;

use App\Models\Gapoktan;
use App\Repositories\ConsolidatedReportRepository;
use Illuminate\Support\Facades\Log;

class ConsolidatedReportService
{
    protected $repository;

    public function __construct(ConsolidatedReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate consolidated income statement.
     */
    public function generateConsolidatedIncomeStatement(int $gapoktanId, string $startDate, string $endDate): array
    {
        try {
            $gapoktan = Gapoktan::find($gapoktanId);

            if (!$gapoktan) {
                return [
                    'success' => false,
                    'message' => 'Gapoktan not found',
                ];
            }

            $data = $this->repository->getConsolidatedIncomeStatement($gapoktanId, $startDate, $endDate);

            return [
                'success' => true,
                'data' => [
                    'gapoktan' => [
                        'id' => $gapoktan->id,
                        'name' => $gapoktan->nama,
                    ],
                    'report_type' => 'Consolidated Income Statement',
                    'period' => $data['period'],
                    'income' => [
                        'categories' => $data['income']['by_category']->map(function ($cat) {
                            return [
                                'category' => $cat['category'],
                                'amount' => $cat['amount'],
                                'formatted_amount' => 'Rp ' . number_format($cat['amount'], 0, ',', '.'),
                                'count' => $cat['count'],
                            ];
                        })->values()->all(),
                        'total' => $data['income']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['income']['total'], 0, ',', '.'),
                        'count' => $data['income']['count'],
                    ],
                    'expense' => [
                        'categories' => $data['expense']['by_category']->map(function ($cat) {
                            return [
                                'category' => $cat['category'],
                                'amount' => $cat['amount'],
                                'formatted_amount' => 'Rp ' . number_format($cat['amount'], 0, ',', '.'),
                                'count' => $cat['count'],
                            ];
                        })->values()->all(),
                        'total' => $data['expense']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['expense']['total'], 0, ',', '.'),
                        'count' => $data['expense']['count'],
                    ],
                    'net_income' => $data['net_income'],
                    'formatted_net_income' => 'Rp ' . number_format($data['net_income'], 0, ',', '.'),
                    'poktan_count' => $data['poktan_count'],
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate consolidated income statement: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate consolidated income statement: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate consolidated cash flow report.
     */
    public function generateConsolidatedCashFlow(int $gapoktanId, string $startDate, string $endDate): array
    {
        try {
            $gapoktan = Gapoktan::find($gapoktanId);

            if (!$gapoktan) {
                return [
                    'success' => false,
                    'message' => 'Gapoktan not found',
                ];
            }

            $data = $this->repository->getConsolidatedCashFlow($gapoktanId, $startDate, $endDate);

            return [
                'success' => true,
                'data' => [
                    'gapoktan' => [
                        'id' => $gapoktan->id,
                        'name' => $gapoktan->nama,
                    ],
                    'report_type' => 'Consolidated Cash Flow',
                    'period' => $data['period'],
                    'opening_balance' => $data['opening_balance'],
                    'formatted_opening_balance' => 'Rp ' . number_format($data['opening_balance'], 0, ',', '.'),
                    'cash_inflows' => [
                        'total' => $data['cash_inflows']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['cash_inflows']['total'], 0, ',', '.'),
                        'count' => $data['cash_inflows']['count'],
                    ],
                    'cash_outflows' => [
                        'total' => $data['cash_outflows']['total'],
                        'formatted_total' => 'Rp ' . number_format($data['cash_outflows']['total'], 0, ',', '.'),
                        'count' => $data['cash_outflows']['count'],
                    ],
                    'net_cash_flow' => $data['net_cash_flow'],
                    'formatted_net_cash_flow' => 'Rp ' . number_format($data['net_cash_flow'], 0, ',', '.'),
                    'closing_balance' => $data['closing_balance'],
                    'formatted_closing_balance' => 'Rp ' . number_format($data['closing_balance'], 0, ',', '.'),
                    'poktan_count' => $data['poktan_count'],
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate consolidated cash flow: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate consolidated cash flow: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate consolidated balance sheet.
     */
    public function generateConsolidatedBalanceSheet(int $gapoktanId, string $asOfDate): array
    {
        try {
            $gapoktan = Gapoktan::find($gapoktanId);

            if (!$gapoktan) {
                return [
                    'success' => false,
                    'message' => 'Gapoktan not found',
                ];
            }

            $data = $this->repository->getConsolidatedBalanceSheet($gapoktanId, $asOfDate);

            return [
                'success' => true,
                'data' => [
                    'gapoktan' => [
                        'id' => $gapoktan->id,
                        'name' => $gapoktan->nama,
                    ],
                    'report_type' => 'Consolidated Balance Sheet',
                    'as_of_date' => $data['as_of_date'],
                    'assets' => [
                        'cash' => $data['assets']['cash'],
                        'formatted_cash' => 'Rp ' . number_format($data['assets']['cash'], 0, ',', '.'),
                        'by_poktan' => $data['assets']['by_poktan']->map(function ($item) {
                            return [
                                'poktan_id' => $item['poktan_id'],
                                'poktan_name' => $item['poktan_name'],
                                'balance' => $item['balance'],
                                'formatted_balance' => 'Rp ' . number_format($item['balance'], 0, ',', '.'),
                            ];
                        })->values()->all(),
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
                    ],
                    'poktan_count' => $data['poktan_count'],
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate consolidated balance sheet: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate consolidated balance sheet: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate consolidated transaction summary.
     */
    public function generateConsolidatedTransactionSummary(int $gapoktanId, string $startDate, string $endDate, array $filters = []): array
    {
        try {
            $gapoktan = Gapoktan::find($gapoktanId);

            if (!$gapoktan) {
                return [
                    'success' => false,
                    'message' => 'Gapoktan not found',
                ];
            }

            $data = $this->repository->getConsolidatedTransactionSummary($gapoktanId, $startDate, $endDate, $filters);

            return [
                'success' => true,
                'data' => [
                    'gapoktan' => [
                        'id' => $gapoktan->id,
                        'name' => $gapoktan->nama,
                    ],
                    'report_type' => 'Consolidated Transaction Summary',
                    'period' => $data['period'],
                    'summary' => [
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
                            'poktan_id' => $trx['poktan_id'],
                            'poktan_name' => $trx['poktan_name'],
                            'transaction_date' => $trx['date'],
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
                    'poktan_count' => $data['poktan_count'],
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate consolidated transaction summary: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate consolidated transaction summary: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate Poktan comparison report.
     */
    public function generatePoktanComparison(int $gapoktanId, string $startDate, string $endDate): array
    {
        try {
            $gapoktan = Gapoktan::find($gapoktanId);

            if (!$gapoktan) {
                return [
                    'success' => false,
                    'message' => 'Gapoktan not found',
                ];
            }

            $data = $this->repository->getPoktanComparison($gapoktanId, $startDate, $endDate);

            return [
                'success' => true,
                'data' => [
                    'gapoktan' => [
                        'id' => $gapoktan->id,
                        'name' => $gapoktan->nama,
                    ],
                    'report_type' => 'Poktan Comparison',
                    'period' => $data['period'],
                    'comparison' => $data['comparison']->map(function ($poktan, $index) {
                        return [
                            'rank' => $index + 1,
                            'poktan_id' => $poktan['poktan_id'],
                            'poktan_name' => $poktan['poktan_name'],
                            'income' => $poktan['income'],
                            'formatted_income' => 'Rp ' . number_format($poktan['income'], 0, ',', '.'),
                            'expense' => $poktan['expense'],
                            'formatted_expense' => 'Rp ' . number_format($poktan['expense'], 0, ',', '.'),
                            'net_income' => $poktan['net_income'],
                            'formatted_net_income' => 'Rp ' . number_format($poktan['net_income'], 0, ',', '.'),
                            'transaction_count' => $poktan['transaction_count'],
                            'current_balance' => $poktan['current_balance'],
                            'formatted_current_balance' => 'Rp ' . number_format($poktan['current_balance'], 0, ',', '.'),
                        ];
                    })->values()->all(),
                    'summary' => [
                        'total_income' => $data['summary']['total_income'],
                        'formatted_total_income' => 'Rp ' . number_format($data['summary']['total_income'], 0, ',', '.'),
                        'total_expense' => $data['summary']['total_expense'],
                        'formatted_total_expense' => 'Rp ' . number_format($data['summary']['total_expense'], 0, ',', '.'),
                        'total_net_income' => $data['summary']['total_net_income'],
                        'formatted_total_net_income' => 'Rp ' . number_format($data['summary']['total_net_income'], 0, ',', '.'),
                        'total_transactions' => $data['summary']['total_transactions'],
                        'total_balance' => $data['summary']['total_balance'],
                        'formatted_total_balance' => 'Rp ' . number_format($data['summary']['total_balance'], 0, ',', '.'),
                        'average_income' => $data['summary']['average_income'],
                        'formatted_average_income' => 'Rp ' . number_format($data['summary']['average_income'], 0, ',', '.'),
                        'average_expense' => $data['summary']['average_expense'],
                        'formatted_average_expense' => 'Rp ' . number_format($data['summary']['average_expense'], 0, ',', '.'),
                        'average_net_income' => $data['summary']['average_net_income'],
                        'formatted_average_net_income' => 'Rp ' . number_format($data['summary']['average_net_income'], 0, ',', '.'),
                    ],
                    'poktan_count' => $data['poktan_count'],
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate Poktan comparison: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate Poktan comparison: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate Gapoktan summary dashboard.
     */
    public function generateGapoktanSummary(int $gapoktanId): array
    {
        try {
            $gapoktan = Gapoktan::find($gapoktanId);

            if (!$gapoktan) {
                return [
                    'success' => false,
                    'message' => 'Gapoktan not found',
                ];
            }

            $data = $this->repository->getGapoktanSummary($gapoktanId);

            return [
                'success' => true,
                'data' => [
                    'gapoktan' => [
                        'id' => $gapoktan->id,
                        'name' => $gapoktan->nama,
                        'code' => $gapoktan->code ?? 'N/A',
                    ],
                    'report_type' => 'Gapoktan Summary Dashboard',
                    'poktan_count' => $data['gapoktan_info']['poktan_count'],
                    'current_month' => [
                        'month' => $data['current_month']['month'],
                        'income' => $data['current_month']['income'],
                        'formatted_income' => 'Rp ' . number_format($data['current_month']['income'], 0, ',', '.'),
                        'income_count' => $data['current_month']['income_count'],
                        'expense' => $data['current_month']['expense'],
                        'formatted_expense' => 'Rp ' . number_format($data['current_month']['expense'], 0, ',', '.'),
                        'expense_count' => $data['current_month']['expense_count'],
                        'net' => $data['current_month']['net_income'],
                        'formatted_net' => 'Rp ' . number_format($data['current_month']['net_income'], 0, ',', '.'),
                    ],
                    'all_time' => [
                        'total_income' => $data['all_time']['total_income'],
                        'formatted_total_income' => 'Rp ' . number_format($data['all_time']['total_income'], 0, ',', '.'),
                        'total_expense' => $data['all_time']['total_expense'],
                        'formatted_total_expense' => 'Rp ' . number_format($data['all_time']['total_expense'], 0, ',', '.'),
                        'net' => $data['all_time']['net_income'],
                        'formatted_net' => 'Rp ' . number_format($data['all_time']['net_income'], 0, ',', '.'),
                        'current_balance' => $data['all_time']['total_balance'],
                        'formatted_current_balance' => 'Rp ' . number_format($data['all_time']['total_balance'], 0, ',', '.'),
                    ],
                    'pending_transactions' => [
                        'count' => $data['pending_transactions']['count'],
                        'total_amount' => $data['pending_transactions']['total_amount'],
                        'formatted_total_amount' => 'Rp ' . number_format($data['pending_transactions']['total_amount'], 0, ',', '.'),
                    ],
                    'recent_activity' => $data['recent_activity']->map(function ($trx) {
                        return [
                            'id' => $trx['id'],
                            'poktan_name' => $trx['poktan_name'],
                            'date' => $trx['date'],
                            'type' => ucfirst($trx['type']),
                            'category' => $trx['category'],
                            'amount' => $trx['amount'],
                            'formatted_amount' => 'Rp ' . number_format($trx['amount'], 0, ',', '.'),
                            'description' => $trx['description'],
                        ];
                    })->values()->all(),
                    'generated_at' => $data['gapoktan_info']['generated_at'],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate Gapoktan summary: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate Gapoktan summary: ' . $e->getMessage(),
            ];
        }
    }
}
