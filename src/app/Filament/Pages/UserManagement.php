<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class UserManagement extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    
    protected static ?string $navigationLabel = 'Manajemen User';
    
    protected static ?string $title = 'Manajemen User & Poktan';
    
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.user-management';
    
    public static function getNavigationGroup(): ?string
    {
        return 'User & Poktan';
    }
}
