<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Livewire\Attributes\Computed;

class FinancialReports extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected string $view = 'filament.pages.financial-reports';
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;
    
    protected static ?string $navigationLabel = 'Laporan Keuangan';
    
    protected static ?string $title = 'Laporan Keuangan';
    
    protected static ?int $navigationSort = 2;
    
    public static function getNavigationGroup(): ?string
    {
        return 'Keuangan';
    }
    
    public string $activeTab = 'transactions';
    
    public function mount(): void
    {
        $this->activeTab = request()->get('tab', 'transactions');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::query()->with(['category', 'poktan']))
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                    
                TextColumn::make('poktan.name')
                    ->label('Poktan')
                    ->searchable(),
                    
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),
                    
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50),
                    
                TextColumn::make('transaction_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'income' => 'Masuk',
                        'expense' => 'Keluar',
                    }),
                    
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->color(fn ($record): string => 
                        $record->transaction_type === 'income' ? 'success' : 'danger'
                    ),
                    
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('transaction_type')
                    ->label('Tipe Transaksi')
                    ->options([
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'approved' => 'Disetujui',
                        'pending' => 'Menunggu',
                        'rejected' => 'Ditolak',
                    ]),
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->poll('30s');
    }
    
    public function getIncomeStatement(): array
    {
        $currentMonth = now()->format('Y-m');
        
        // Income categories
        $incomeCategories = TransactionCategory::where('type', 'income')->get();
        $incomeData = [];
        $totalIncome = 0;
        
        foreach ($incomeCategories as $category) {
            $amount = Transaction::where('category_id', $category->id)
                ->where('status', 'approved')
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$currentMonth])
                ->sum('amount');
            
            if ($amount > 0) {
                $incomeData[] = [
                    'category' => $category->name,
                    'amount' => $amount,
                ];
                $totalIncome += $amount;
            }
        }
        
        // Expense categories
        $expenseCategories = TransactionCategory::where('type', 'expense')->get();
        $expenseData = [];
        $totalExpense = 0;
        
        foreach ($expenseCategories as $category) {
            $amount = Transaction::where('category_id', $category->id)
                ->where('status', 'approved')
                ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$currentMonth])
                ->sum('amount');
            
            if ($amount > 0) {
                $expenseData[] = [
                    'category' => $category->name,
                    'amount' => $amount,
                ];
                $totalExpense += $amount;
            }
        }
        
        return [
            'income' => $incomeData,
            'expense' => $expenseData,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_income' => $totalIncome - $totalExpense,
            'period' => now()->format('F Y'),
        ];
    }
    
    public function getCategorySummary(): array
    {
        return TransactionCategory::select('name', 'type')
            ->withSum(['transactions' => function ($query) {
                $query->where('status', 'approved')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
            }], 'amount')
            ->having('transactions_sum_amount', '>', 0)
            ->orderBy('transactions_sum_amount', 'desc')
            ->get()
            ->toArray();
    }
}
