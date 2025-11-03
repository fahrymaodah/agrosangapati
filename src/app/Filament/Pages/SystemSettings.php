<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SystemSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    
    protected static ?string $navigationLabel = 'System Settings';
    
    protected static ?string $title = 'System Settings & Configuration';
    
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.system-settings';
    
    public static function getNavigationGroup(): ?string
    {
        return 'System Settings';
    }
}
