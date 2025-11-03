<?php

namespace App\Filament\Resources\HarvestResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class HarvestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('poktan.name')
                    ->label('Poktan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commodity.name')
                    ->label('Komoditas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('farmer_name')
                    ->label('Petani')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->suffix(' kg')
                    ->sortable(),
                TextColumn::make('harvest_date')
                    ->label('Tanggal Panen')
                    ->date()
                    ->sortable(),
                TextColumn::make('quality_grade')
                    ->label('Kualitas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'warning',
                        'C' => 'gray',
                        default => 'primary',
                    }),
                TextColumn::make('price_per_kg')
                    ->label('Harga/Kg')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_value')
                    ->label('Total Nilai')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->quantity * $record->price_per_kg)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('poktan_id')
                    ->label('Poktan')
                    ->relationship('poktan', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('commodity_id')
                    ->label('Komoditas')
                    ->relationship('commodity', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('quality_grade')
                    ->label('Kualitas')
                    ->options([
                        'A' => 'Grade A',
                        'B' => 'Grade B',
                        'C' => 'Grade C',
                    ]),
                Filter::make('harvest_date')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('harvest_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('harvest_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('harvest_date', 'desc');
    }
}