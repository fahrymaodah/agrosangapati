<?php

namespace App\Filament\Resources\GapoktanResource\Pages;

use App\Filament\Resources\GapoktanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGapoktan extends EditRecord
{
    protected static string $resource = GapoktanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
