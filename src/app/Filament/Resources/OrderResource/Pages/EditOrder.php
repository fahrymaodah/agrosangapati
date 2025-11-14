<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculate total_amount from items
        $totalAmount = 0;
        
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                // Recalculate subtotal to make sure it's correct
                $quantity = floatval($item['quantity'] ?? 0);
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $subtotal = $quantity * $unitPrice;
                
                $totalAmount += $subtotal;
            }
        }
        
        $data['total_amount'] = $totalAmount;
        
        // Calculate grand_total
        $shippingCost = floatval($data['shipping_cost'] ?? 0);
        $data['grand_total'] = $totalAmount + $shippingCost;
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Recalculate totals after items are saved (relationship items saved AFTER order update)
        $order = $this->record;
        
        // Calculate total from saved items
        $totalAmount = $order->items()->sum('subtotal');
        
        // Update order totals
        $order->update([
            'total_amount' => $totalAmount,
            'grand_total' => $totalAmount + $order->shipping_cost,
        ]);
    }
}