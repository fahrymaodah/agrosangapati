<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ProductionDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;
    
    protected static ?string $navigationLabel = 'Dashboard Produksi';
    
    protected static ?string $title = 'Dashboard Produksi';
    
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.production-dashboard';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Hasil Bumi';
    }
}
