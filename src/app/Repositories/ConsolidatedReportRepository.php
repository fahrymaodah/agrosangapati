<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\CashBalance;
use App\Models\CashBalanceHistory;
use App\Models\Poktan;
use Illuminate\Support\Facades\DB;

class ConsolidatedReportRepository
{
    /**
     * Get consolidated income statement for all Poktan in a Gapoktan.
     */
    public function getConsolidatedIncomeStatement(int $gapoktanId, string $startDate, string $endDate): array
    {
        // Get all Poktan under this Gapoktan
        $poktanIds = Poktan::where('gapoktan_id', $gapoktanId)->pluck('id');

        // Get consolidated income by category
        $income = Transaction::whereIn('transactions.poktan_id', $poktanIds)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->leftJoin('transaction_categories', 'transactions.category_id', '=', 'transaction_categories.id')
            ->select(
                'transaction_categories.name as category',
                'transactions.poktan_id',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->groupBy('transactions.category_id', 'transaction_categories.name', 'transactions.poktan_id')
            ->get();

        // Get consolidated expense by category
        $expense = Transaction::whereIn('transactions.poktan_id', $poktanIds)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->leftJoin('transaction_categories', 'transactions.category_id', '=', 'transaction_categories.id')
            ->select(
                'transaction_categories.name as category',
                'transactions.poktan_id',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->groupBy('transactions.category_id', 'transaction_categories.name', 'transactions.poktan_id')
            ->get();

        // Group by Poktan
        $incomeByPoktan = $income->groupBy('poktan_id');
        $expenseByPoktan = $expense->groupBy('poktan_id');

        // Calculate totals
        $totalIncome = $income->sum('total');
        $totalExpense = $expense->sum('total');

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'income' => [
                'by_poktan' => $incomeByPoktan,
                'by_category' => $income->groupBy('category')->map(function ($items) {
                    return [
                        'category' => $items->first()->category,
                        'amount' => $items->sum('total'),
                        'count' => $items->count(),
                    ];
                })->values(),
                'total' => $totalIncome,
                'count' => $income->count(),
            ],
            'expense' => [
                'by_poktan' => $expenseByPoktan,
                'by_category' => $expense->groupBy('category')->map(function ($items) {
                    return [
                        'category' => $items->first()->category,
                        'amount' => $items->sum('total'),
                        'count' => $items->count(),
                    ];
                })->values(),
                'total' => $totalExpense,
                'count' => $expense->count(),
            ],
            'net_income' => $totalIncome - $totalExpense,
            'poktan_count' => $poktanIds->count(),
        ];
    }

    /**
     * Get consolidated cash flow for all Poktan in a Gapoktan.
     */
    public function getConsolidatedCashFlow(int $gapoktanId, string $startDate, string $endDate): array
    {
        $poktanIds = Poktan::where('gapoktan_id', $gapoktanId)->pluck('id');

        // Get opening balances for all Poktan
        $openingBalances = collect();
        foreach ($poktanIds as $poktanId) {
            $balance = $this->getBalanceAtDate($poktanId, $startDate);
            $openingBalances->push([
                'poktan_id' => $poktanId,
                'balance' => $balance,
            ]);
        }

        // Get cash inflows (approved income transactions)
        $inflows = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select('poktan_id', DB::raw('SUM(amount) as total'))
            ->groupBy('poktan_id')
            ->get();

        // Get cash outflows (approved expense transactions)
        $outflows = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select('poktan_id', DB::raw('SUM(amount) as total'))
            ->groupBy('poktan_id')
            ->get();

        // Get current balances
        $closingBalances = CashBalance::whereIn('poktan_id', $poktanIds)
            ->select('poktan_id', 'balance')
            ->get();

        $totalOpeningBalance = $openingBalances->sum('balance');
        $totalInflows = $inflows->sum('total');
        $totalOutflows = $outflows->sum('total');
        $totalClosingBalance = $closingBalances->sum('balance');

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'opening_balance' => $totalOpeningBalance,
            'by_poktan' => [
                'opening' => $openingBalances,
                'inflows' => $inflows,
                'outflows' => $outflows,
                'closing' => $closingBalances,
            ],
            'cash_inflows' => [
                'total' => $totalInflows,
                'count' => $inflows->count(),
                'details' => $inflows,
            ],
            'cash_outflows' => [
                'total' => $totalOutflows,
                'count' => $outflows->count(),
                'details' => $outflows,
            ],
            'net_cash_flow' => $totalInflows - $totalOutflows,
            'closing_balance' => $totalClosingBalance,
            'poktan_count' => $poktanIds->count(),
        ];
    }

    /**
     * Get consolidated balance sheet for all Poktan in a Gapoktan.
     */
    public function getConsolidatedBalanceSheet(int $gapoktanId, string $asOfDate): array
    {
        $poktanIds = Poktan::where('gapoktan_id', $gapoktanId)->pluck('id');

        // Get balance for each Poktan as of specific date
        $poktanBalances = collect();
        foreach ($poktanIds as $poktanId) {
            $balance = $this->getBalanceAtDate($poktanId, $asOfDate);
            $poktanBalances->push([
                'poktan_id' => $poktanId,
                'poktan_name' => Poktan::find($poktanId)->nama,
                'balance' => $balance,
            ]);
        }

        $totalCash = $poktanBalances->sum('balance');

        return [
            'as_of_date' => $asOfDate,
            'assets' => [
                'cash' => $totalCash,
                'by_poktan' => $poktanBalances,
                'total' => $totalCash,
            ],
            'equity' => [
                'initial_capital' => 0, // Gapoktan initial capital if any
                'retained_earnings' => $totalCash,
                'total' => $totalCash,
            ],
            'summary' => [
                'total_income' => Transaction::whereIn('poktan_id', $poktanIds)
                    ->where('transaction_type', 'income')
                    ->where('status', 'approved')
                    ->where('transaction_date', '<=', $asOfDate)
                    ->sum('amount'),
                'total_expense' => Transaction::whereIn('poktan_id', $poktanIds)
                    ->where('transaction_type', 'expense')
                    ->where('status', 'approved')
                    ->where('transaction_date', '<=', $asOfDate)
                    ->sum('amount'),
            ],
            'poktan_count' => $poktanIds->count(),
        ];
    }

    /**
     * Get consolidated transaction summary for all Poktan in a Gapoktan.
     */
    public function getConsolidatedTransactionSummary(int $gapoktanId, string $startDate, string $endDate, array $filters = []): array
    {
        $poktanIds = Poktan::where('gapoktan_id', $gapoktanId)->pluck('id');

        $query = Transaction::whereIn('poktan_id', $poktanIds)
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('transaction_type', $filters['type']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['poktan_id'])) {
            $query->where('poktan_id', $filters['poktan_id']);
        }

        $transactions = $query->with(['category', 'poktan', 'creator', 'approver'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Calculate statistics
        $approvedTransactions = $transactions->where('status', 'approved');
        $totalIncome = $approvedTransactions->where('transaction_type', 'income')->sum('amount');
        $totalExpense = $approvedTransactions->where('transaction_type', 'expense')->sum('amount');

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'statistics' => [
                'total_transactions' => $transactions->count(),
                'approved_count' => $transactions->where('status', 'approved')->count(),
                'pending_count' => $transactions->where('status', 'pending')->count(),
                'rejected_count' => $transactions->where('status', 'rejected')->count(),
                'income_count' => $approvedTransactions->where('transaction_type', 'income')->count(),
                'expense_count' => $approvedTransactions->where('transaction_type', 'expense')->count(),
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_amount' => $totalIncome - $totalExpense,
            ],
            'transactions' => $transactions->map(function ($trx) {
                return [
                    'id' => $trx->id,
                    'poktan_id' => $trx->poktan_id,
                    'poktan_name' => $trx->poktan->nama ?? 'N/A',
                    'date' => $trx->transaction_date->format('Y-m-d'),
                    'type' => $trx->transaction_type,
                    'category' => $trx->category->name ?? 'N/A',
                    'description' => $trx->description,
                    'amount' => $trx->amount,
                    'status' => $trx->status,
                    'created_by' => $trx->creator->name ?? 'N/A',
                    'approved_by' => $trx->approver->name ?? null,
                ];
            }),
            'poktan_count' => $poktanIds->count(),
        ];
    }

    /**
     * Get comparison report between all Poktan in a Gapoktan.
     */
    public function getPoktanComparison(int $gapoktanId, string $startDate, string $endDate): array
    {
        $poktans = Poktan::where('gapoktan_id', $gapoktanId)->get();

        $comparison = collect();

        foreach ($poktans as $poktan) {
            $income = Transaction::where('poktan_id', $poktan->id)
                ->where('transaction_type', 'income')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $expense = Transaction::where('poktan_id', $poktan->id)
                ->where('transaction_type', 'expense')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $transactionCount = Transaction::where('poktan_id', $poktan->id)
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->count();

            $currentBalance = CashBalance::where('poktan_id', $poktan->id)->value('balance') ?? 0;

            $comparison->push([
                'poktan_id' => $poktan->id,
                'poktan_name' => $poktan->nama,
                'income' => $income,
                'expense' => $expense,
                'net_income' => $income - $expense,
                'transaction_count' => $transactionCount,
                'current_balance' => $currentBalance,
            ]);
        }

        // Sort by net income (highest first)
        $sorted = $comparison->sortByDesc('net_income')->values();

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'comparison' => $sorted,
            'summary' => [
                'total_income' => $sorted->sum('income'),
                'total_expense' => $sorted->sum('expense'),
                'total_net_income' => $sorted->sum('net_income'),
                'total_transactions' => $sorted->sum('transaction_count'),
                'total_balance' => $sorted->sum('current_balance'),
                'average_income' => $sorted->avg('income'),
                'average_expense' => $sorted->avg('expense'),
                'average_net_income' => $sorted->avg('net_income'),
            ],
            'poktan_count' => $poktans->count(),
        ];
    }

    /**
     * Get Gapoktan summary dashboard data.
     */
    public function getGapoktanSummary(int $gapoktanId): array
    {
        $poktanIds = Poktan::where('gapoktan_id', $gapoktanId)->pluck('id');
        $poktanCount = $poktanIds->count();

        // Current month stats
        $currentMonth = now()->format('Y-m');
        $currentMonthStart = now()->startOfMonth()->toDateString();
        $currentMonthEnd = now()->endOfMonth()->toDateString();

        $monthlyIncome = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');

        $monthlyIncomeCount = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->count();

        $monthlyExpense = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');

        $monthlyExpenseCount = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->count();

        // All-time stats
        $totalIncome = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->sum('amount');

        $totalExpense = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->sum('amount');

        $totalBalance = CashBalance::whereIn('poktan_id', $poktanIds)->sum('balance');

        // Pending transactions
        $pendingCount = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('status', 'pending')
            ->count();

        $pendingAmount = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('status', 'pending')
            ->sum('amount');

        // Transaction activity
        $recentTransactions = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('status', 'approved')
            ->with(['poktan', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($trx) {
                return [
                    'id' => $trx->id,
                    'poktan_name' => $trx->poktan->nama ?? 'N/A',
                    'date' => $trx->transaction_date,
                    'type' => $trx->transaction_type,
                    'category' => $trx->category->nama ?? 'N/A',
                    'amount' => $trx->amount,
                    'description' => $trx->description,
                ];
            });

        return [
            'gapoktan_info' => [
                'poktan_count' => $poktanCount,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'current_month' => [
                'month' => $currentMonth,
                'income' => $monthlyIncome,
                'income_count' => $monthlyIncomeCount,
                'expense' => $monthlyExpense,
                'expense_count' => $monthlyExpenseCount,
                'net_income' => $monthlyIncome - $monthlyExpense,
            ],
            'all_time' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_income' => $totalIncome - $totalExpense,
                'total_balance' => $totalBalance,
            ],
            'pending_transactions' => [
                'count' => $pendingCount,
                'total_amount' => $pendingAmount,
            ],
            'recent_activity' => $recentTransactions,
        ];
    }

    /**
     * Helper: Get balance at specific date for a Poktan.
     */
    private function getBalanceAtDate(int $poktanId, string $date): float
    {
        // Try to get from balance history first
        $historyBalance = CashBalanceHistory::where('poktan_id', $poktanId)
            ->whereDate('created_at', '<=', $date)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($historyBalance) {
            return $historyBalance->new_balance;
        }

        // If no history, calculate from transactions
        $income = Transaction::where('poktan_id', $poktanId)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereDate('transaction_date', '<=', $date)
            ->sum('amount');

        $expense = Transaction::where('poktan_id', $poktanId)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->whereDate('transaction_date', '<=', $date)
            ->sum('amount');

        return $income - $expense;
    }
}
