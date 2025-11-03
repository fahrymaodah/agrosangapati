<?php

namespace App\Filament\Resources\ShipmentResource\Pages;

use App\Filament\Resources\ShipmentResource;
use App\Filament\Resources\ShipmentResource\Tables\ShipmentsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShipments extends ListRecords
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return ShipmentsTable::table($table);
    }
}
