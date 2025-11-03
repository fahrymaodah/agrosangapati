<?php

namespace App\Filament\Resources\CommodityResource\Pages;

use App\Filament\Resources\CommodityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCommodity extends ViewRecord
{
    protected static string $resource = CommodityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}