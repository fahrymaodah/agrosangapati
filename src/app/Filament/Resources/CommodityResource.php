<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommodityResource\Pages\CreateCommodity;
use App\Filament\Resources\CommodityResource\Pages\EditCommodity;
use App\Filament\Resources\CommodityResource\Pages\ListCommodities;
use App\Filament\Resources\CommodityResource\Pages\ViewCommodity;
use App\Filament\Resources\CommodityResource\Schemas\CommodityForm;
use App\Filament\Resources\CommodityResource\Tables\CommoditiesTable;
use App\Models\Commodity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommodityResource extends Resource
{
    protected static ?string $model = Commodity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $navigationLabel = 'Komoditas';

    protected static ?string $modelLabel = 'Komoditas';

    protected static ?string $pluralModelLabel = 'Komoditas';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Hasil Bumi';
    }

    public static function form(Schema $schema): Schema
    {
        return CommodityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommoditiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommodities::route('/'),
            'create' => CreateCommodity::route('/create'),
            'view' => ViewCommodity::route('/{record}'),
            'edit' => EditCommodity::route('/{record}/edit'),
        ];
    }
}