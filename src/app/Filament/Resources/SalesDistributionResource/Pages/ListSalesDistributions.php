<?php

namespace App\Filament\Resources\SalesDistributionResource\Pages;

use App\Filament\Resources\SalesDistributionResource;
use App\Filament\Resources\SalesDistributionResource\Tables\SalesDistributionsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesDistributions extends ListRecords
{
    protected static string $resource = SalesDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return SalesDistributionsTable::table($table);
    }
}
