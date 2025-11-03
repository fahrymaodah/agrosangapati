<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HarvestResource\Pages\CreateHarvest;
use App\Filament\Resources\HarvestResource\Pages\EditHarvest;
use App\Filament\Resources\HarvestResource\Pages\ListHarvests;
use App\Filament\Resources\HarvestResource\Pages\ViewHarvest;
use App\Filament\Resources\HarvestResource\Schemas\HarvestForm;
use App\Filament\Resources\HarvestResource\Tables\HarvestsTable;
use App\Models\Harvest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HarvestResource extends Resource
{
    protected static ?string $model = Harvest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Data Panen';

    protected static ?string $modelLabel = 'Data Panen';

    protected static ?string $pluralModelLabel = 'Data Panen';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Hasil Bumi';
    }

    public static function form(Schema $schema): Schema
    {
        return HarvestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HarvestsTable::configure($table);
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
            'index' => ListHarvests::route('/'),
            'create' => CreateHarvest::route('/create'),
            'view' => ViewHarvest::route('/{record}'),
            'edit' => EditHarvest::route('/{record}/edit'),
        ];
    }
}