<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Transactions\Widgets\FinancialStatsWidget;
use App\Filament\Resources\Transactions\Widgets\IncomeStatementChartWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class FinancialDashboard extends Page
{
    protected string $view = 'filament.pages.financial-dashboard';
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;
    
    protected static ?string $navigationLabel = 'Dashboard Keuangan';
    
    protected static ?string $title = 'Dashboard Keuangan';
    
    protected static ?int $navigationSort = 1;
    
    public static function getNavigationGroup(): ?string
    {
        return 'Keuangan';
    }

    /**
     * Get the widgets that should be displayed on the page.
     */
    public function getHeaderWidgets(): array
    {
        return [
            FinancialStatsWidget::class,
            IncomeStatementChartWidget::class,
        ];
    }
}
