<?php

namespace App\Filament\Resources\StockResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StocksTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('commodity.name')
                    ->label('Komoditas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('poktan.name')
                    ->label('Poktan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric(0)
                    ->suffix(' kg')
                    ->sortable()
                    ->color(fn ($record) => $record->quantity <= ($record->min_stock_alert ?? 10) ? 'danger' : 'success'),

                BadgeColumn::make('quality_grade')
                    ->label('Grade')
                    ->colors([
                        'success' => 'A',
                        'warning' => 'B',
                        'danger' => 'C',
                    ]),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'reserved',
                        'danger' => 'damaged',
                        'secondary' => 'sold',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'reserved' => 'Direservasi',
                        'sold' => 'Terjual',
                        'damaged' => 'Rusak',
                        default => $state,
                    }),

                TextColumn::make('harvest_date')
                    ->label('Tanggal Panen')
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'reserved' => 'Direservasi',
                        'sold' => 'Terjual',
                        'damaged' => 'Rusak',
                    ]),

                SelectFilter::make('quality_grade')
                    ->label('Grade Kualitas')
                    ->options([
                        'A' => 'Grade A',
                        'B' => 'Grade B',
                        'C' => 'Grade C',
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
            ->defaultSort('created_at', 'desc');
    }
}
