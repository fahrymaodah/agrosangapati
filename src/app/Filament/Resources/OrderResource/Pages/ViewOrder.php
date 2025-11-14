<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Shipment;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Support\Facades\DB;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            
            // Process Order Action
            Action::make('process')
                ->label('Proses Pesanan')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Proses Pesanan')
                ->modalDescription('Apakah Anda yakin ingin memproses pesanan ini? Stok produk akan dikurangi.')
                ->modalSubmitActionLabel('Ya, Proses')
                ->visible(fn (Order $record) => $record->order_status === 'pending')
                ->action(function (Order $record) {
                    try {
                        DB::beginTransaction();
                        
                        // Check stock availability for all items
                        foreach ($record->items as $item) {
                            $product = $item->product;
                            if ($product->stock_quantity < $item->quantity) {
                                throw new \Exception("Stok tidak cukup untuk produk: {$product->name}. Tersedia: {$product->stock_quantity}, Dibutuhkan: {$item->quantity}");
                            }
                        }
                        
                        // Reduce stock for each item
                        foreach ($record->items as $item) {
                            $product = $item->product;
                            $product->stock_quantity -= $item->quantity;
                            $product->save();
                        }
                        
                        // Update order status
                        $record->order_status = 'processing';
                        $record->save();
                        
                        DB::commit();
                        
                        Notification::make()
                            ->success()
                            ->title('Pesanan Diproses')
                            ->body('Pesanan berhasil diproses dan stok telah dikurangi.')
                            ->send();
                            
                    } catch (\Exception $e) {
                        DB::rollBack();
                        
                        Notification::make()
                            ->danger()
                            ->title('Gagal Memproses Pesanan')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->after(fn () => $this->refreshFormData(['order_status'])),
            
            // Ship Order Action
            Action::make('ship')
                ->label('Kirim Pesanan')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->form([
                    TextInput::make('tracking_number')
                        ->label('Nomor Resi')
                        ->required()
                        ->maxLength(255)
                        ->default(fn () => 'SHIP-' . date('Ymd') . '-' . strtoupper(substr(md5(time()), 0, 6))),
                    TextInput::make('courier_name')
                        ->label('Kurir')
                        ->required()
                        ->maxLength(255)
                        ->default('JNE'),
                    DateTimePicker::make('shipping_date')
                        ->label('Tanggal Pengiriman')
                        ->required()
                        ->default(now())
                        ->native(false),
                    DateTimePicker::make('estimated_arrival')
                        ->label('Estimasi Tiba')
                        ->native(false),
                    Textarea::make('notes')
                        ->label('Catatan Pengiriman')
                        ->rows(3),
                ])
                ->visible(fn (Order $record) => $record->order_status === 'processing')
                ->action(function (Order $record, array $data) {
                    try {
                        DB::beginTransaction();
                        
                        // Create shipment record
                        Shipment::create([
                            'order_id' => $record->id,
                            'tracking_number' => $data['tracking_number'],
                            'courier_name' => $data['courier_name'],
                            'shipping_date' => $data['shipping_date'],
                            'estimated_arrival' => $data['estimated_arrival'] ?? null,
                            'shipment_status' => 'in_transit',
                            'notes' => $data['notes'] ?? null,
                        ]);
                        
                        // Update order status
                        $record->order_status = 'shipped';
                        $record->save();
                        
                        DB::commit();
                        
                        Notification::make()
                            ->success()
                            ->title('Pesanan Dikirim')
                            ->body('Pesanan berhasil dikirim dengan resi: ' . $data['tracking_number'])
                            ->send();
                            
                    } catch (\Exception $e) {
                        DB::rollBack();
                        
                        Notification::make()
                            ->danger()
                            ->title('Gagal Mengirim Pesanan')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->after(fn () => $this->refreshFormData(['order_status'])),
            
            // Complete Order Action
            Action::make('complete')
                ->label('Selesaikan Pesanan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Selesaikan Pesanan')
                ->modalDescription('Apakah pesanan ini sudah diterima pelanggan dengan baik?')
                ->modalSubmitActionLabel('Ya, Selesaikan')
                ->visible(fn (Order $record) => $record->order_status === 'shipped')
                ->action(function (Order $record) {
                    $record->order_status = 'delivered';
                    $record->save();
                    
                    // Update shipment status if exists
                    if ($record->shipment) {
                        $record->shipment->shipment_status = 'delivered';
                        $record->shipment->actual_arrival = now();
                        $record->shipment->save();
                    }
                    
                    Notification::make()
                        ->success()
                        ->title('Pesanan Selesai')
                        ->body('Pesanan telah selesai dan diterima pelanggan.')
                        ->send();
                })
                ->after(fn () => $this->refreshFormData(['order_status'])),
            
            // Cancel Order Action
            Action::make('cancel')
                ->label('Batalkan Pesanan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('cancellation_reason')
                        ->label('Alasan Pembatalan')
                        ->required()
                        ->rows(3),
                ])
                ->visible(fn (Order $record) => in_array($record->order_status, ['pending', 'processing']))
                ->action(function (Order $record, array $data) {
                    try {
                        DB::beginTransaction();
                        
                        // If order was already processed, restore stock
                        if ($record->order_status === 'processing') {
                            foreach ($record->items as $item) {
                                $product = $item->product;
                                $product->stock_quantity += $item->quantity;
                                $product->save();
                            }
                        }
                        
                        // Update order status
                        $record->order_status = 'cancelled';
                        $record->notes = ($record->notes ? $record->notes . "\n\n" : '') . 
                                       "DIBATALKAN: " . $data['cancellation_reason'];
                        $record->save();
                        
                        DB::commit();
                        
                        Notification::make()
                            ->success()
                            ->title('Pesanan Dibatalkan')
                            ->body('Pesanan berhasil dibatalkan dan stok dikembalikan.')
                            ->send();
                            
                    } catch (\Exception $e) {
                        DB::rollBack();
                        
                        Notification::make()
                            ->danger()
                            ->title('Gagal Membatalkan Pesanan')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->after(fn () => $this->refreshFormData(['order_status'])),
        ];
    }
}