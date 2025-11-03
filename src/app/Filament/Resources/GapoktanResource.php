<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GapoktanResource\Pages;
use App\Models\Gapoktan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class GapoktanResource extends Resource
{
    protected static ?string $model = Gapoktan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Gapoktan';

    protected static ?string $modelLabel = 'Gapoktan';

    protected static ?string $pluralModelLabel = 'Gapoktan';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'User & Poktan';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGapoktans::route('/'),
            'create' => Pages\CreateGapoktan::route('/create'),
            'view' => Pages\ViewGapoktan::route('/{record}'),
            'edit' => Pages\EditGapoktan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            return static::getModel()::where('status', 'active')->count();
        } catch (\Exception $e) {
            return null; // Return null if table/column doesn't exist yet
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
