<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert status enum to is_active toggle boolean
        $data['is_active'] = ($data['status'] ?? 'active') === 'active';
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert is_active toggle boolean to status enum
        // Toggle sends true/false, so we check explicitly
        $data['status'] = ($data['is_active'] ?? false) === true ? 'active' : 'inactive';
        unset($data['is_active']);
        
        return $data;
    }
}