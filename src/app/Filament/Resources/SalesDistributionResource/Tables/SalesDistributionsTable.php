<?php

namespace App\Filament\Resources\SalesDistributionResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesDistributionsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('distribution_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('poktan.name')
                    ->label('Poktan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_sales')
                    ->label('Total Penjualan')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('farmer_share_percentage')
                    ->label('% Petani')
                    ->numeric(1)
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('farmer_share_amount')
                    ->label('Bagian Petani')
                    ->money('IDR')
                    ->sortable()
                    ->color('success'),

                TextColumn::make('organization_share_amount')
                    ->label('Bagian Organisasi')
                    ->money('IDR')
                    ->sortable()
                    ->color('info')
                    ->toggleable(),

                BadgeColumn::make('payment_status')
                    ->label('Status Bayar')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'processing',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'processing' => 'Diproses',
                        'paid' => 'Dibayar',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'check' => 'Cek',
                        'other' => 'Lainnya',
                        default => '-',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment_date')
                    ->label('Tgl Bayar')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Diproses',
                        'paid' => 'Dibayar',
                        'cancelled' => 'Dibatalkan',
                    ]),

                SelectFilter::make('poktan_id')
                    ->label('Poktan')
                    ->relationship('poktan', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('distribution_date', 'desc');
    }
}
