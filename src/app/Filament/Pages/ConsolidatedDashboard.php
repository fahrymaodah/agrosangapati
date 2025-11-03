<?php

namespace App\Filament\Pages;

use App\Models\Poktan;
use App\Models\Transaction;
use App\Models\CashBalance;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class ConsolidatedDashboard extends Page
{
    protected string $view = 'filament.pages.consolidated-dashboard';
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;
    
    protected static ?string $navigationLabel = 'Dashboard Konsolidasi';
    
    protected static ?string $title = 'Dashboard Konsolidasi Gapoktan';
    
    protected static ?int $navigationSort = 3;
    
    public static function getNavigationGroup(): ?string
    {
        return 'Keuangan';
    }

    public function getPoktanSummary(): array
    {
        $currentMonth = now()->format('Y-m');
        
        return Poktan::select('id', 'name')
            ->withSum(['transactions as total_income' => function ($query) use ($currentMonth) {
                $query->where('transaction_type', 'income')
                    ->where('status', 'approved')
                    ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$currentMonth]);
            }], 'amount')
            ->withSum(['transactions as total_expense' => function ($query) use ($currentMonth) {
                $query->where('transaction_type', 'expense')
                    ->where('status', 'approved')
                    ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$currentMonth]);
            }], 'amount')
            ->get()
            ->map(function ($poktan) {
                $income = $poktan->total_income ?? 0;
                $expense = $poktan->total_expense ?? 0;
                
                // Get current balance from CashBalance model
                $currentBalance = CashBalance::where('poktan_id', $poktan->id)
                    ->latest('updated_at')
                    ->first();
                $balance = $currentBalance?->balance ?? 0;
                
                return [
                    'name' => $poktan->name,
                    'income' => $income,
                    'expense' => $expense,
                    'net_income' => $income - $expense,
                    'balance' => $balance,
                ];
            })
            ->toArray();
    }
    
    public function getTopPerformers(): array
    {
        return collect($this->getPoktanSummary())
            ->sortByDesc('net_income')
            ->take(5)
            ->values()
            ->toArray();
    }
    
    public function getConsolidatedStats(): array
    {
        $poktanSummary = $this->getPoktanSummary();
        
        return [
            'total_poktans' => count($poktanSummary),
            'total_income' => collect($poktanSummary)->sum('income'),
            'total_expense' => collect($poktanSummary)->sum('expense'),
            'total_balance' => collect($poktanSummary)->sum('balance'),
            'avg_income' => collect($poktanSummary)->avg('income'),
            'profitable_poktans' => collect($poktanSummary)->where('net_income', '>', 0)->count(),
        ];
    }
}
