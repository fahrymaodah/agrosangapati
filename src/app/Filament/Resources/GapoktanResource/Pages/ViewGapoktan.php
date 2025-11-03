<?php

namespace App\Filament\Resources\GapoktanResource\Pages;

use App\Filament\Resources\GapoktanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGapoktan extends ViewRecord
{
    protected static string $resource = GapoktanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
