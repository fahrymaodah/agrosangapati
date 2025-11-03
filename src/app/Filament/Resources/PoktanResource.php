<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoktanResource\Pages\CreatePoktan;
use App\Filament\Resources\PoktanResource\Pages\EditPoktan;
use App\Filament\Resources\PoktanResource\Pages\ListPoktans;
use App\Filament\Resources\PoktanResource\Pages\ViewPoktan;
use App\Filament\Resources\PoktanResource\Schemas\PoktanForm;
use App\Filament\Resources\PoktanResource\Tables\PoktansTable;
use App\Models\Poktan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PoktanResource extends Resource
{
    protected static ?string $model = Poktan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Kelompok Tani';

    protected static ?string $modelLabel = 'Kelompok Tani';

    protected static ?string $pluralModelLabel = 'Kelompok Tani';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'User & Poktan';
    }

    public static function form(Schema $schema): Schema
    {
        return PoktanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PoktansTable::configure($table);
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
            'index' => ListPoktans::route('/'),
            'create' => CreatePoktan::route('/create'),
            'view' => ViewPoktan::route('/{record}'),
            'edit' => EditPoktan::route('/{record}/edit'),
        ];
    }
}