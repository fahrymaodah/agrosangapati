<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\CashBalance;
use App\Models\CashBalanceHistory;
use Illuminate\Support\Facades\DB;

class FinancialReportRepository
{
    /**
     * Get income statement data for a poktan.
     */
    public function getIncomeStatement(int $poktanId, string $startDate, string $endDate): array
    {
        // Get income transactions
        $incomes = Transaction::where('poktan_id', $poktanId)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with('category')
            ->get();

        // Get expense transactions
        $expenses = Transaction::where('poktan_id', $poktanId)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with('category')
            ->get();

        // Group by category
        $incomeByCategory = $incomes->groupBy('category_id')->map(function ($transactions) {
            return [
                'category_name' => $transactions->first()->category->nama,
                'total' => $transactions->sum('amount'),
                'count' => $transactions->count(),
            ];
        })->values();

        $expenseByCategory = $expenses->groupBy('category_id')->map(function ($transactions) {
            return [
                'category_name' => $transactions->first()->category->nama,
                'total' => $transactions->sum('amount'),
                'count' => $transactions->count(),
            ];
        })->values();

        $totalIncome = $incomes->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $netIncome = $totalIncome - $totalExpense;

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'income' => [
                'by_category' => $incomeByCategory,
                'total' => $totalIncome,
                'count' => $incomes->count(),
            ],
            'expense' => [
                'by_category' => $expenseByCategory,
                'total' => $totalExpense,
                'count' => $expenses->count(),
            ],
            'net_income' => $netIncome,
        ];
    }

    /**
     * Get cash flow report for a poktan.
     */
    public function getCashFlowReport(int $poktanId, string $startDate, string $endDate): array
    {
        // Get opening balance
        $openingBalance = $this->getBalanceAtDate($poktanId, $startDate);

        // Get all balance changes in period
        $balanceChanges = CashBalanceHistory::where('poktan_id', $poktanId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['transaction.category'])
            ->orderBy('created_at')
            ->get();

        // Group by type
        $cashInflows = $balanceChanges->where('type', 'income');
        $cashOutflows = $balanceChanges->where('type', 'expense');

        // Get closing balance
        $closingBalance = $this->getBalanceAtDate($poktanId, $endDate);

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'opening_balance' => $openingBalance,
            'cash_inflows' => [
                'items' => $cashInflows->map(function ($item) {
                    return [
                        'date' => $item->created_at->format('Y-m-d'),
                        'description' => $item->description,
                        'category' => $item->transaction->category->nama ?? 'N/A',
                        'amount' => $item->amount,
                    ];
                })->values(),
                'total' => $cashInflows->sum('amount'),
                'count' => $cashInflows->count(),
            ],
            'cash_outflows' => [
                'items' => $cashOutflows->map(function ($item) {
                    return [
                        'date' => $item->created_at->format('Y-m-d'),
                        'description' => $item->description,
                        'category' => $item->transaction->category->nama ?? 'N/A',
                        'amount' => $item->amount,
                    ];
                })->values(),
                'total' => $cashOutflows->sum('amount'),
                'count' => $cashOutflows->count(),
            ],
            'net_cash_flow' => $cashInflows->sum('amount') - $cashOutflows->sum('amount'),
            'closing_balance' => $closingBalance,
        ];
    }

    /**
     * Get balance sheet data for a poktan.
     */
    public function getBalanceSheet(int $poktanId, string $asOfDate): array
    {
        $balance = $this->getBalanceAtDate($poktanId, $asOfDate);

        // Get total income and expense (all time)
        $totalIncome = Transaction::where('poktan_id', $poktanId)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->where('transaction_date', '<=', $asOfDate)
            ->sum('amount');

        $totalExpense = Transaction::where('poktan_id', $poktanId)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->where('transaction_date', '<=', $asOfDate)
            ->sum('amount');

        return [
            'as_of_date' => $asOfDate,
            'assets' => [
                'cash' => $balance,
                'total' => $balance,
            ],
            'equity' => [
                'initial_capital' => 0, // TODO: Add initial capital tracking
                'retained_earnings' => $balance,
                'total' => $balance,
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'current_balance' => $balance,
            ],
        ];
    }

    /**
     * Get transaction summary for a poktan.
     */
    public function getTransactionSummary(int $poktanId, string $startDate, string $endDate, array $filters = []): array
    {
        $query = Transaction::where('poktan_id', $poktanId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['category', 'creator', 'approver']);

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('transaction_type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        // Calculate statistics
        $approved = $transactions->where('status', 'approved');
        $pending = $transactions->where('status', 'pending');
        $rejected = $transactions->where('status', 'rejected');

        $income = $approved->where('transaction_type', 'income');
        $expense = $approved->where('transaction_type', 'expense');

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'statistics' => [
                'total_transactions' => $transactions->count(),
                'approved_count' => $approved->count(),
                'pending_count' => $pending->count(),
                'rejected_count' => $rejected->count(),
                'income_count' => $income->count(),
                'expense_count' => $expense->count(),
                'total_income' => $income->sum('amount'),
                'total_expense' => $expense->sum('amount'),
                'net_amount' => $income->sum('amount') - $expense->sum('amount'),
            ],
            'transactions' => $transactions->map(function ($trx) {
                return [
                    'id' => $trx->id,
                    'date' => $trx->transaction_date,
                    'type' => $trx->transaction_type,
                    'category' => $trx->category->nama,
                    'description' => $trx->description,
                    'amount' => $trx->amount,
                    'status' => $trx->status,
                    'created_by' => $trx->creator->name ?? 'N/A',
                    'approved_by' => $trx->approver->name ?? 'N/A',
                ];
            })->values(),
        ];
    }

    /**
     * Get detailed monthly report for a poktan.
     */
    public function getMonthlyDetailedReport(int $poktanId, int $year, int $month): array
    {
        $startDate = sprintf('%d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        // Get opening balance (last day of previous month)
        $previousMonthEnd = date('Y-m-d', strtotime($startDate . ' -1 day'));
        $openingBalance = $this->getBalanceAtDate($poktanId, $previousMonthEnd);

        // Get all transactions
        $transactions = Transaction::where('poktan_id', $poktanId)
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['category', 'creator'])
            ->orderBy('transaction_date')
            ->get();

        // Daily breakdown
        $dailyBreakdown = $transactions->groupBy(function ($trx) {
            return $trx->transaction_date;
        })->map(function ($dayTransactions, $date) {
            $income = $dayTransactions->where('transaction_type', 'income')->sum('amount');
            $expense = $dayTransactions->where('transaction_type', 'expense')->sum('amount');

            return [
                'date' => $date,
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
                'transaction_count' => $dayTransactions->count(),
            ];
        })->values();

        // Get closing balance
        $closingBalance = $this->getBalanceAtDate($poktanId, $endDate);

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'month_name' => date('F', strtotime($startDate)),
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_income' => $transactions->where('transaction_type', 'income')->sum('amount'),
            'total_expense' => $transactions->where('transaction_type', 'expense')->sum('amount'),
            'net_change' => $closingBalance - $openingBalance,
            'transaction_count' => $transactions->count(),
            'daily_breakdown' => $dailyBreakdown,
        ];
    }

    /**
     * Get comparative report (month over month or year over year).
     */
    public function getComparativeReport(int $poktanId, string $period1Start, string $period1End, string $period2Start, string $period2End): array
    {
        $period1 = $this->getIncomeStatement($poktanId, $period1Start, $period1End);
        $period2 = $this->getIncomeStatement($poktanId, $period2Start, $period2End);

        return [
            'period_1' => [
                'range' => $period1['period'],
                'total_income' => $period1['income']['total'],
                'total_expense' => $period1['expense']['total'],
                'net_income' => $period1['net_income'],
            ],
            'period_2' => [
                'range' => $period2['period'],
                'total_income' => $period2['income']['total'],
                'total_expense' => $period2['expense']['total'],
                'net_income' => $period2['net_income'],
            ],
            'variance' => [
                'income' => $period2['income']['total'] - $period1['income']['total'],
                'expense' => $period2['expense']['total'] - $period1['expense']['total'],
                'net_income' => $period2['net_income'] - $period1['net_income'],
            ],
            'percentage_change' => [
                'income' => $period1['income']['total'] > 0 
                    ? (($period2['income']['total'] - $period1['income']['total']) / $period1['income']['total']) * 100 
                    : 0,
                'expense' => $period1['expense']['total'] > 0 
                    ? (($period2['expense']['total'] - $period1['expense']['total']) / $period1['expense']['total']) * 100 
                    : 0,
                'net_income' => $period1['net_income'] != 0 
                    ? (($period2['net_income'] - $period1['net_income']) / abs($period1['net_income'])) * 100 
                    : 0,
            ],
        ];
    }

    /**
     * Get balance at specific date.
     */
    protected function getBalanceAtDate(int $poktanId, string $date): float
    {
        // Get last balance history entry before or on the date
        $lastHistory = CashBalanceHistory::where('poktan_id', $poktanId)
            ->where('created_at', '<=', $date . ' 23:59:59')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastHistory) {
            return $lastHistory->new_balance;
        }

        // If no history, return current balance if date is today or future
        if ($date >= date('Y-m-d')) {
            $cashBalance = CashBalance::where('poktan_id', $poktanId)->first();
            return $cashBalance ? $cashBalance->balance : 0;
        }

        return 0;
    }
}
