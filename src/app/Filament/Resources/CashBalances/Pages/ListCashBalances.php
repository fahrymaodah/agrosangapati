<?php

namespace App\Filament\Resources\CashBalances\Pages;

use App\Filament\Resources\CashBalances\CashBalanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCashBalances extends ListRecords
{
    protected static string $resource = CashBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
