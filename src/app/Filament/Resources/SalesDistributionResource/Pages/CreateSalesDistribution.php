<?php

namespace App\Filament\Resources\SalesDistributionResource\Pages;

use App\Filament\Resources\SalesDistributionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesDistribution extends CreateRecord
{
    protected static string $resource = SalesDistributionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate reference number if not provided
        if (empty($data['reference_number'])) {
            $data['reference_number'] = 'DIST-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        }

        return $data;
    }
}
