<?php

namespace App\Filament\Resources\HarvestResource\Pages;

use App\Filament\Resources\HarvestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHarvest extends ViewRecord
{
    protected static string $resource = HarvestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}