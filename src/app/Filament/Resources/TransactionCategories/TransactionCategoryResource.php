<?php

namespace App\Filament\Resources\TransactionCategories;

use App\Filament\Resources\TransactionCategories\Pages\CreateTransactionCategory;
use App\Filament\Resources\TransactionCategories\Pages\EditTransactionCategory;
use App\Filament\Resources\TransactionCategories\Pages\ListTransactionCategories;
use App\Filament\Resources\TransactionCategories\Schemas\TransactionCategoryForm;
use App\Filament\Resources\TransactionCategories\Tables\TransactionCategoriesTable;
use App\Models\TransactionCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionCategoryResource extends Resource
{
    protected static ?string $model = TransactionCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Kategori Transaksi';

    protected static ?string $modelLabel = 'Kategori Transaksi';

    protected static ?string $pluralModelLabel = 'Kategori Transaksi';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Keuangan';
    }

    public static function form(Schema $schema): Schema
    {
        return TransactionCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionCategoriesTable::configure($table);
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
            'index' => ListTransactionCategories::route('/'),
            'create' => CreateTransactionCategory::route('/create'),
            'edit' => EditTransactionCategory::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
