<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\CashBalance;
use App\Models\Poktan;
use App\Models\Gapoktan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardRepository
{
    /**
     * Get financial summary for a Poktan
     */
    public function getPoktanFinancialSummary(int $poktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Total income and expense
        $income = Transaction::where('poktan_id', $poktanId)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $expense = Transaction::where('poktan_id', $poktanId)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Current balance
        $balance = CashBalance::where('poktan_id', $poktanId)->first();

        // Pending transactions count
        $pendingCount = Transaction::where('poktan_id', $poktanId)
            ->where('status', 'pending')
            ->count();

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'net_income' => $income - $expense,
            'current_balance' => $balance ? $balance->balance : 0,
            'pending_transactions' => $pendingCount,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    /**
     * Get financial summary for Gapoktan (consolidated from all Poktans)
     */
    public function getGapoktanFinancialSummary(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        // Get all poktans under this gapoktan
        $poktanIds = Poktan::where('gapoktan_id', $gapoktanId)->pluck('id');

        // Total income and expense from all poktans
        $income = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $expense = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Total balance from all poktans
        $totalBalance = CashBalance::whereIn('poktan_id', $poktanIds)->sum('balance');

        // Pending transactions count across all poktans
        $pendingCount = Transaction::whereIn('poktan_id', $poktanIds)
            ->where('status', 'pending')
            ->count();

        // Count poktans
        $poktanCount = $poktanIds->count();

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'net_income' => $income - $expense,
            'total_balance' => $totalBalance,
            'pending_transactions' => $pendingCount,
            'poktan_count' => $poktanCount,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    /**
     * Get monthly trend for last 6 months (Poktan level)
     */
    public function getPoktanMonthlyTrend(int $poktanId, int $months = 6): array
    {
        $trends = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $date->copy()->endOfMonth()->format('Y-m-d');

            $income = Transaction::where('poktan_id', $poktanId)
                ->where('transaction_type', 'income')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $expense = Transaction::where('poktan_id', $poktanId)
                ->where('transaction_type', 'expense')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $trends[] = [
                'month' => $date->format('M Y'),
                'month_num' => $date->format('Y-m'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
        }

        return $trends;
    }

    /**
     * Get monthly trend for Gapoktan (consolidated)
     */
    public function getGapoktanMonthlyTrend(int $gapoktanId, int $months = 6): array
    {
        $poktanIds = Poktan::where('gapoktan_id', $gapoktanId)->pluck('id');
        $trends = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $date->copy()->endOfMonth()->format('Y-m-d');

            $income = Transaction::whereIn('poktan_id', $poktanIds)
                ->where('transaction_type', 'income')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $expense = Transaction::whereIn('poktan_id', $poktanIds)
                ->where('transaction_type', 'expense')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $trends[] = [
                'month' => $date->format('M Y'),
                'month_num' => $date->format('Y-m'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
        }

        return $trends;
    }

    /**
     * Get recent transactions (Poktan level)
     */
    public function getRecentTransactions(int $poktanId, int $limit = 10): array
    {
        return Transaction::where('poktan_id', $poktanId)
            ->with(['category', 'creator', 'approver'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get recent transactions for Gapoktan (all poktans)
     */
    public function getGapoktanRecentTransactions(int $gapoktanId, int $limit = 10): array
    {
        $poktanIds = Poktan::where('gapoktan_id', $gapoktanId)->pluck('id');

        return Transaction::whereIn('poktan_id', $poktanIds)
            ->with(['category', 'creator', 'approver', 'poktan'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get pending approval transactions (Poktan level)
     */
    public function getPendingApprovals(int $poktanId): array
    {
        return Transaction::where('poktan_id', $poktanId)
            ->where('status', 'pending')
            ->with(['category', 'creator'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get pending approval transactions for Gapoktan
     */
    public function getGapoktanPendingApprovals(int $gapoktanId): array
    {
        $poktanIds = Poktan::where('gapoktan_id', $gapoktanId)->pluck('id');

        return Transaction::whereIn('poktan_id', $poktanIds)
            ->where('status', 'pending')
            ->with(['category', 'creator', 'poktan'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get category breakdown (Poktan level)
     */
    public function getCategoryBreakdown(int $poktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $breakdown = Transaction::where('poktan_id', $poktanId)
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select('transaction_type', 'category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('transaction_type', 'category_id')
            ->with('category')
            ->get()
            ->groupBy('transaction_type')
            ->toArray();

        return $breakdown;
    }
}
