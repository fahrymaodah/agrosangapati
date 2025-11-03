<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesDistributionResource\Pages;
use App\Models\SalesDistribution;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class SalesDistributionResource extends Resource
{
    protected static ?string $model = SalesDistribution::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Distribusi Penjualan';

    protected static ?string $modelLabel = 'Distribusi Penjualan';

    protected static ?string $pluralModelLabel = 'Distribusi Penjualan';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return 'Pemasaran';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesDistributions::route('/'),
            'create' => Pages\CreateSalesDistribution::route('/create'),
            'view' => Pages\ViewSalesDistribution::route('/{record}'),
            'edit' => Pages\EditSalesDistribution::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            return static::getModel()::where('payment_status', 'pending')->count();
        } catch (\Exception $e) {
            return null; // Return null if table/column doesn't exist yet
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
