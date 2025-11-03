<?php

namespace App\Filament\Resources\GapoktanResource\Pages;

use App\Filament\Resources\GapoktanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGapoktan extends CreateRecord
{
    protected static string $resource = GapoktanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
