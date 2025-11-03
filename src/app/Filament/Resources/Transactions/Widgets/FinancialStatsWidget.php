<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Models\Transaction;
use App\Models\CashBalance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class FinancialStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Current month data
        $currentMonth = now()->format('Y-m');
        
        // Total Income this month
        $totalIncome = Transaction::where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$currentMonth])
            ->sum('amount');
        
        // Total Expense this month
        $totalExpense = Transaction::where('transaction_type', 'expense')
            ->where('status', 'approved')
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$currentMonth])
            ->sum('amount');
        
        // Net Income
        $netIncome = $totalIncome - $totalExpense;
        
        // Total Cash Balance
        $totalCashBalance = CashBalance::sum('balance');
        
        // Previous month for comparison
        $previousMonth = now()->subMonth()->format('Y-m');
        $previousIncome = Transaction::where('transaction_type', 'income')
            ->where('status', 'approved')
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$previousMonth])
            ->sum('amount');

        return [
            Stat::make('Total Pemasukan Bulan Ini', 'Rp ' . number_format($totalIncome, 0, ',', '.'))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 12, 15, 18, 22, 25, 30]),
            
            Stat::make('Total Pengeluaran Bulan Ini', 'Rp ' . number_format($totalExpense, 0, ',', '.'))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([30, 25, 22, 18, 15, 12, 7]),
            
            Stat::make('Laba/Rugi Bersih', 'Rp ' . number_format($netIncome, 0, ',', '.'))
                ->description($netIncome >= 0 ? 'Laba bersih' : 'Rugi bersih')
                ->descriptionIcon($netIncome >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($netIncome >= 0 ? 'success' : 'danger'),
            
            Stat::make('Total Saldo Kas', 'Rp ' . number_format($totalCashBalance, 0, ',', '.'))
                ->description('Saldo kas semua poktan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
