<?php

namespace App\Filament\Resources\PoktanResource\Pages;

use App\Filament\Resources\PoktanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPoktan extends ViewRecord
{
    protected static string $resource = PoktanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}