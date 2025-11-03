<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ActivityLog extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    
    protected static ?string $navigationLabel = 'Activity Log';
    
    protected static ?string $title = 'Activity Log & Audit Trail';
    
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.activity-log';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Activity Log';
    }
}
