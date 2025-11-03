<?php

namespace App\Filament\Resources\GapoktanResource\Pages;

use App\Filament\Resources\GapoktanResource;
use App\Filament\Resources\GapoktanResource\Tables\GapoktansTable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGapoktans extends ListRecords
{
    protected static string $resource = GapoktanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return GapoktansTable::table($table);
    }
}
