<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class IncomeStatementChartWidget extends ChartWidget
{
    protected ?string $heading = 'Laporan Laba Rugi (6 Bulan Terakhir)';
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get last 6 months data
        $months = [];
        $incomeData = [];
        $expenseData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthYear = $date->format('Y-m');
            $monthName = $date->format('M Y');
            
            $months[] = $monthName;
            
            // Income for this month
            $income = Transaction::where('transaction_type', 'income')
                ->where('status', 'approved')
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$monthYear])
                ->sum('amount');
            
            // Expense for this month
            $expense = Transaction::where('transaction_type', 'expense')
                ->where('status', 'approved')
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$monthYear])
                ->sum('amount');
            
            $incomeData[] = floatval($income);
            $expenseData[] = floatval($expense);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#059669',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#dc2626',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
        ];
    }
}
