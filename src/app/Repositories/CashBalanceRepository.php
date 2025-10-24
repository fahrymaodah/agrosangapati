<?php

namespace App\Repositories;

use App\Models\CashBalance;
use App\Models\CashBalanceHistory;
use App\Models\Poktan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CashBalanceRepository
{
    /**
     * Get cash balance for a specific poktan.
     */
    public function getBalanceByPoktan(int $poktanId): ?CashBalance
    {
        return CashBalance::with('poktan')
            ->where('poktan_id', $poktanId)
            ->first();
    }

    /**
     * Get all cash balances with poktan information.
     */
    public function getAllBalances(): Collection
    {
        return CashBalance::with('poktan')
            ->orderBy('balance', 'desc')
            ->get();
    }

    /**
     * Get balance history for a specific poktan.
     */
    public function getBalanceHistory(int $poktanId, array $filters = []): Collection
    {
        $query = CashBalanceHistory::with(['poktan', 'transaction', 'creator'])
            ->forPoktan($poktanId);

        // Filter by type (income/expense)
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by date range
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get recent balance changes for a poktan.
     */
    public function getRecentChanges(int $poktanId, int $limit = 10): Collection
    {
        return CashBalanceHistory::with(['transaction', 'creator'])
            ->forPoktan($poktanId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get poktans with low balance.
     */
    public function getLowBalancePoktans(float $threshold = 1000000): Collection
    {
        return CashBalance::with('poktan')
            ->where('balance', '<', $threshold)
            ->where('balance', '>', 0)
            ->orderBy('balance', 'asc')
            ->get();
    }

    /**
     * Get poktans with zero or negative balance.
     */
    public function getZeroBalancePoktans(): Collection
    {
        return CashBalance::with('poktan')
            ->where('balance', '<=', 0)
            ->get();
    }

    /**
     * Get balance statistics for a poktan within a date range.
     */
    public function getBalanceStatistics(int $poktanId, string $startDate, string $endDate): array
    {
        $histories = CashBalanceHistory::forPoktan($poktanId)
            ->dateRange($startDate, $endDate)
            ->get();

        $totalIncome = $histories->where('type', 'income')->sum('amount');
        $totalExpense = $histories->where('type', 'expense')->sum('amount');
        $netChange = $totalIncome - $totalExpense;

        $currentBalance = $this->getBalanceByPoktan($poktanId);

        return [
            'poktan_id' => $poktanId,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'current_balance' => $currentBalance ? $currentBalance->balance : 0,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_change' => $netChange,
            'transaction_count' => $histories->count(),
            'income_count' => $histories->where('type', 'income')->count(),
            'expense_count' => $histories->where('type', 'expense')->count(),
        ];
    }

    /**
     * Get monthly balance summary for a poktan.
     */
    public function getMonthlyBalanceSummary(int $poktanId, int $year): array
    {
        $monthlySummary = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = sprintf('%d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));

            $histories = CashBalanceHistory::forPoktan($poktanId)
                ->dateRange($startDate, $endDate)
                ->get();

            $totalIncome = $histories->where('type', 'income')->sum('amount');
            $totalExpense = $histories->where('type', 'expense')->sum('amount');

            $monthlySummary[] = [
                'month' => $month,
                'month_name' => date('F', strtotime($startDate)),
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net' => $totalIncome - $totalExpense,
                'transaction_count' => $histories->count(),
            ];
        }

        return $monthlySummary;
    }

    /**
     * Get balance trend data for charts.
     */
    public function getBalanceTrend(int $poktanId, int $days = 30): array
    {
        $endDate = now();
        $startDate = now()->subDays($days);

        $histories = CashBalanceHistory::forPoktan($poktanId)
            ->dateRange($startDate, $endDate)
            ->orderBy('created_at', 'asc')
            ->get();

        $trendData = [];
        
        foreach ($histories as $history) {
            $trendData[] = [
                'date' => $history->created_at->format('Y-m-d H:i:s'),
                'balance' => $history->new_balance,
                'change' => $history->amount,
                'type' => $history->type,
            ];
        }

        return $trendData;
    }

    /**
     * Get all poktans with their current balances.
     */
    public function getAllPoktansWithBalance(): Collection
    {
        return Poktan::leftJoin('cash_balances', 'poktans.id', '=', 'cash_balances.poktan_id')
            ->select('poktans.*', 'cash_balances.balance', 'cash_balances.last_updated')
            ->orderBy('poktans.name')
            ->get();
    }

    /**
     * Check if poktan has sufficient balance.
     */
    public function hasSufficientBalance(int $poktanId, float $amount): bool
    {
        $balance = $this->getBalanceByPoktan($poktanId);
        
        return $balance && $balance->balance >= $amount;
    }

    /**
     * Get total balance across all poktans.
     */
    public function getTotalBalance(): float
    {
        return CashBalance::sum('balance');
    }

    /**
     * Create initial balance for a poktan.
     */
    public function createInitialBalance(int $poktanId, float $initialAmount, int $createdBy): CashBalance
    {
        $balance = CashBalance::create([
            'poktan_id' => $poktanId,
            'balance' => $initialAmount,
            'last_updated' => now(),
        ]);

        // Record history if initial amount > 0
        if ($initialAmount > 0) {
            CashBalanceHistory::create([
                'poktan_id' => $poktanId,
                'transaction_id' => null,
                'previous_balance' => 0,
                'amount' => $initialAmount,
                'new_balance' => $initialAmount,
                'type' => 'income',
                'description' => 'Initial balance',
                'created_by' => $createdBy,
            ]);
        }

        return $balance;
    }
}
