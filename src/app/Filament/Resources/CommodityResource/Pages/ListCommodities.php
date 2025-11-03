<?php

namespace App\Filament\Resources\CommodityResource\Pages;

use App\Filament\Resources\CommodityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommodities extends ListRecords
{
    protected static string $resource = CommodityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}