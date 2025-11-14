<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    
    protected ?string $heading = 'Tren Pendapatan & Pengeluaran';
    
    protected int | string | array $columnSpan = 'full';
    
    protected ?string $maxHeight = '500px';

    protected function getData(): array
    {
        // Get data for last 12 months
        $months = collect(range(0, 11))->map(function ($month) {
            return Carbon::now()->subMonths(11 - $month);
        });

        $incomeData = $months->map(function ($date) {
            return Transaction::where('transaction_type', 'income')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');
        })->toArray();

        $expenseData = $months->map(function ($date) {
            return Transaction::where('transaction_type', 'expense')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');
        })->toArray();

        $labels = $months->map(function ($date) {
            return $date->format('M Y');
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
