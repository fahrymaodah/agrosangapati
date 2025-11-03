<?php

namespace App\Filament\Resources\TransactionCategories\Pages;

use App\Filament\Resources\TransactionCategories\TransactionCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTransactionCategory extends EditRecord
{
    protected static string $resource = TransactionCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn ($record) => $record->is_default)
                ->requiresConfirmation()
                ->modalHeading('Hapus Kategori Transaksi')
                ->modalDescription('Apakah Anda yakin ingin menghapus kategori ini? Data tidak dapat dikembalikan.')
                ->modalSubmitActionLabel('Ya, Hapus'),
            ForceDeleteAction::make()
                ->hidden(fn ($record) => $record->is_default),
            RestoreAction::make(),
        ];
    }
}
