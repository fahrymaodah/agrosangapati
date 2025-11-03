<?php

namespace App\Filament\Resources\CashBalances;

use App\Filament\Resources\CashBalances\Pages\ListCashBalances;
use App\Filament\Resources\CashBalances\Pages\ViewCashBalance;
use App\Filament\Resources\CashBalances\Schemas\CashBalanceInfolist;
use App\Filament\Resources\CashBalances\Tables\CashBalancesTable;
use App\Models\CashBalance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashBalanceResource extends Resource
{
    protected static ?string $model = CashBalance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Saldo Kas';

    protected static ?string $modelLabel = 'Saldo Kas';

    protected static ?string $pluralModelLabel = 'Saldo Kas';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Keuangan';
    }

    public static function canCreate(): bool
    {
        return false; // Read-only resource
    }

    public static function canEdit($record): bool
    {
        return false; // Read-only resource
    }

    public static function canDelete($record): bool
    {
        return false; // Read-only resource
    }

    public static function table(Table $table): Table
    {
        return CashBalancesTable::configure($table);
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
            'index' => ListCashBalances::route('/'),
            'view' => ViewCashBalance::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->with(['poktan.gapoktan']);
    }
}

