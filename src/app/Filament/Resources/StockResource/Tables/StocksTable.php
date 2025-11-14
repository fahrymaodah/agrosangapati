<?php

namespace App\Filament\Resources\StockResource\Tables;

use Filament\Forms;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class StocksTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('commodity.name')
                    ->label('Komoditas')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-cube')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('quality_grade')
                    ->label('Grade')
                    ->badge()
                    ->colors([
                        'success' => 'A',
                        'warning' => 'B',
                        'danger' => 'C',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('poktan.name')
                    ->label('Poktan/Lokasi')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->location ?? '-')
                    ->icon('heroicon-o-map-pin'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Stok Tersedia')
                    ->numeric(2)
                    ->suffix(fn ($record) => ' ' . ($record->unit ?? 'kg'))
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => 
                        $record->quantity <= 0 ? 'danger' :
                        ($record->quantity <= ($record->min_stock_alert ?? 10) ? 'warning' : 'success')
                    )
                    ->icon(fn ($record) => 
                        $record->quantity <= 0 ? 'heroicon-o-x-circle' :
                        ($record->quantity <= ($record->min_stock_alert ?? 10) ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                    )
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'reserved',
                        'danger' => 'damaged',
                        'gray' => 'sold',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'reserved' => 'Direservasi',
                        'sold' => 'Terjual',
                        'damaged' => 'Rusak',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('harvest_date')
                    ->label('Tanggal Panen')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('last_updated')
                    ->label('Update Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->since()
                    ->description(fn ($record) => $record->last_updated?->format('d M Y H:i')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('poktan_id')
                    ->label('Filter Poktan')
                    ->relationship('poktan', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('commodity_id')
                    ->label('Filter Komoditas')
                    ->relationship('commodity', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('quality_grade')
                    ->label('Grade Kualitas')
                    ->options([
                        'A' => 'Grade A',
                        'B' => 'Grade B',
                        'C' => 'Grade C',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Stok')
                    ->options([
                        'active' => 'Aktif',
                        'reserved' => 'Direservasi',
                        'sold' => 'Terjual',
                        'damaged' => 'Rusak',
                    ])
                    ->multiple()
                    ->default(['active']),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis')
                    ->query(fn (Builder $query) => $query->whereRaw('quantity <= min_stock_alert'))
                    ->toggle(),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Stok Habis')
                    ->query(fn (Builder $query) => $query->where('quantity', '<=', 0))
                    ->toggle(),
            ])
            ->bulkActions([
                Actions\BulkAction::make('updateStatus')
                    ->label('Ubah Status')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status Baru')
                            ->options([
                                'active' => 'Aktif',
                                'reserved' => 'Direservasi',
                                'sold' => 'Terjual',
                                'damaged' => 'Rusak',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $records->each(function ($record) use ($data) {
                            $record->update([
                                'status' => $data['status'],
                                'last_updated' => now(),
                            ]);
                        });

                        Notification::make()
                            ->success()
                            ->title('Status Diperbarui')
                            ->body(count($records) . ' stok berhasil diperbarui.')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Actions\BulkAction::make('export')
                    ->label('Export ke Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        // TODO: Implement export functionality
                        Notification::make()
                            ->info()
                            ->title('Export')
                            ->body('Fitur export akan segera tersedia.')
                            ->send();
                    }),

                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('last_updated', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
