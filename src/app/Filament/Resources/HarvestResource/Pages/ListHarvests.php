<?php

namespace App\Filament\Resources\HarvestResource\Pages;

use App\Filament\Resources\HarvestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHarvests extends ListRecords
{
    protected static string $resource = HarvestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}