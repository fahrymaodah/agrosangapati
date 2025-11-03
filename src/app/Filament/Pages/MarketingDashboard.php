<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MarketingDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    
    protected static ?string $navigationLabel = 'Dashboard Pemasaran';
    
    protected static ?string $title = 'Dashboard Pemasaran';
    
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.marketing-dashboard';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Pemasaran';
    }
}
