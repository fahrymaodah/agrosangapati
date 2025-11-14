<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convert is_active toggle boolean to status enum
        // Toggle sends true/false, so we check explicitly
        $data['status'] = ($data['is_active'] ?? true) === true ? 'active' : 'inactive';
        unset($data['is_active']);
        
        return $data;
    }
}