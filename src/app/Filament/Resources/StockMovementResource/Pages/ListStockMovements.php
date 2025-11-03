<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use App\Filament\Resources\StockMovementResource\Tables\StockMovementsTable;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return StockMovementsTable::table($table);
    }
}
