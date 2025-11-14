<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $now = now();
        $lastMonth = now()->subMonth();
        
        // Total Pendapatan (bulan ini)
        $revenueThisMonth = Transaction::where('transaction_type', 'income')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');
        $revenueLastMonth = Transaction::where('transaction_type', 'income')
            ->whereMonth('transaction_date', $lastMonth->month)
            ->whereYear('transaction_date', $lastMonth->year)
            ->sum('amount');
        $revenueChange = $revenueLastMonth > 0 
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100) 
            : ($revenueThisMonth > 0 ? 100 : 0);
        
        // Total Pengeluaran (bulan ini)
        $expenseThisMonth = Transaction::where('transaction_type', 'expense')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');
        $expenseLastMonth = Transaction::where('transaction_type', 'expense')
            ->whereMonth('transaction_date', $lastMonth->month)
            ->whereYear('transaction_date', $lastMonth->year)
            ->sum('amount');
        $expenseChange = $expenseLastMonth > 0 
            ? round((($expenseThisMonth - $expenseLastMonth) / $expenseLastMonth) * 100) 
            : ($expenseThisMonth > 0 ? 100 : 0);
        
        // Menunggu Persetujuan
        $pendingApprovals = Transaction::where('status', 'pending')->count();
        
        // Pengguna Aktif
        $activeUsers = User::where('status', 'active')->count();
        $totalUsers = User::count();
        $activePercentage = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0;
        
        return [
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($revenueThisMonth, 0, ',', '.'))
                ->description($revenueChange >= 0 
                    ? "+{$revenueChange}% dari bulan lalu" 
                    : "{$revenueChange}% dari bulan lalu")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([3, 5, 7, 9, 11, 8, 10, 12]),
                
            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($expenseThisMonth, 0, ',', '.'))
                ->description($expenseChange >= 0 
                    ? "+{$expenseChange}% dari bulan lalu" 
                    : "{$expenseChange}% dari bulan lalu")
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($expenseChange >= 0 ? 'danger' : 'success')
                ->chart([5, 7, 6, 8, 5, 6, 7, 4]),
                
            Stat::make('Menunggu Persetujuan', number_format($pendingApprovals, 0, ',', '.'))
                ->description('Transaksi perlu direview')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Pengguna Aktif', number_format($activeUsers, 0, ',', '.'))
                ->description("{$activePercentage}% dari total pengguna")
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart([5, 6, 7, 6, 8, 9, 10, 9]),
        ];
    }
}
