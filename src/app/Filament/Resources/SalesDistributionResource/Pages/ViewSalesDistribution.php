<?php

namespace App\Filament\Resources\SalesDistributionResource\Pages;

use App\Filament\Resources\SalesDistributionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesDistribution extends ViewRecord
{
    protected static string $resource = SalesDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
