<?php

namespace App\Filament\Resources\ShipmentResource\Pages;

use App\Filament\Resources\ShipmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShipment extends CreateRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate tracking number if not provided
        if (empty($data['tracking_number'])) {
            $data['tracking_number'] = 'SHP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        }

        return $data;
    }
}
