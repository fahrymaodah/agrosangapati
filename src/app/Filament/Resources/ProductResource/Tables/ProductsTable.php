<?php

namespace App\Filament\Resources\ProductResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label('Gambar')
                    ->circular()
                    ->stacked()
                    ->limit(1),

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                BadgeColumn::make('category')
                    ->label('Kategori')
                    ->colors([
                        'secondary' => 'raw',
                        'success' => 'processed',
                        'info' => 'packaged',
                        'warning' => 'organic',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'raw' => 'Mentah',
                        'processed' => 'Olahan',
                        'packaged' => 'Kemasan',
                        'organic' => 'Organik',
                        default => $state,
                    }),

                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Stok')
                    ->numeric(0)
                    ->sortable()
                    ->color(fn ($record) => $record->stock_quantity > 10 ? 'success' : ($record->stock_quantity > 0 ? 'warning' : 'danger')),

                TextColumn::make('unit')
                    ->label('Satuan')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'raw' => 'Mentah',
                        'processed' => 'Olahan',
                        'packaged' => 'Kemasan',
                        'organic' => 'Organik',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua produk')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                SelectFilter::make('commodity_id')
                    ->label('Komoditas')
                    ->relationship('commodity', 'name')
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
