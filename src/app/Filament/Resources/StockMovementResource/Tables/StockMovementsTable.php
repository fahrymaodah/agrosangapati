<?php

namespace App\Filament\Resources\StockMovementResource\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                BadgeColumn::make('movement_type')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                        'warning' => 'transfer',
                        'info' => 'adjustment',
                        'danger' => 'damage',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Penyesuaian',
                        'damage' => 'Kerusakan',
                        default => $state,
                    }),

                TextColumn::make('stock.commodity.name')
                    ->label('Komoditas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric(0)
                    ->suffix(' kg')
                    ->sortable(),

                TextColumn::make('from_location')
                    ->label('Dari')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('to_location')
                    ->label('Ke')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('reference_type')
                    ->label('Referensi')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('movement_type')
                    ->label('Jenis Pergerakan')
                    ->options([
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Penyesuaian',
                        'damage' => 'Kerusakan',
                    ]),

                SelectFilter::make('reference_type')
                    ->label('Tipe Referensi')
                    ->options([
                        'harvest' => 'Panen',
                        'order' => 'Pesanan',
                        'shipment' => 'Pengiriman',
                        'manual' => 'Manual',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
