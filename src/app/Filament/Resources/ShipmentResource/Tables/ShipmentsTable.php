<?php

namespace App\Filament\Resources\ShipmentResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShipmentsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tracking_number')
                    ->label('No. Resi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('order.order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('courier')
                    ->label('Kurir')
                    ->colors([
                        'success' => 'jne',
                        'info' => 'jnt',
                        'warning' => 'sicepat',
                        'primary' => 'anteraja',
                        'secondary' => 'pos',
                        'gray' => 'internal',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'jne' => 'JNE',
                        'jnt' => 'J&T',
                        'sicepat' => 'SiCepat',
                        'anteraja' => 'AnterAja',
                        'pos' => 'POS',
                        'internal' => 'Internal',
                        default => strtoupper($state),
                    }),

                TextColumn::make('recipient_name')
                    ->label('Penerima')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('weight')
                    ->label('Berat')
                    ->numeric(1)
                    ->suffix(' kg')
                    ->sortable(),

                TextColumn::make('shipping_cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'processing',
                        'info' => 'in_transit',
                        'success' => 'delivered',
                        'danger' => ['returned', 'cancelled'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'processing' => 'Diproses',
                        'in_transit' => 'Dalam Pengiriman',
                        'delivered' => 'Terkirim',
                        'returned' => 'Dikembalikan',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                TextColumn::make('shipped_at')
                    ->label('Tgl Kirim')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('estimated_arrival')
                    ->label('Estimasi Tiba')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivered_at')
                    ->label('Tgl Diterima')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                ImageColumn::make('proof_of_delivery')
                    ->label('Bukti')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Diproses',
                        'in_transit' => 'Dalam Pengiriman',
                        'delivered' => 'Terkirim',
                        'returned' => 'Dikembalikan',
                        'cancelled' => 'Dibatalkan',
                    ]),

                SelectFilter::make('courier')
                    ->label('Kurir')
                    ->options([
                        'jne' => 'JNE',
                        'jnt' => 'J&T Express',
                        'sicepat' => 'SiCepat',
                        'anteraja' => 'AnterAja',
                        'pos' => 'POS Indonesia',
                        'internal' => 'Internal',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
