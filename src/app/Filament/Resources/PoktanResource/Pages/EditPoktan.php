<?php

namespace App\Filament\Resources\PoktanResource\Pages;

use App\Filament\Resources\PoktanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPoktan extends EditRecord
{
    protected static string $resource = PoktanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}