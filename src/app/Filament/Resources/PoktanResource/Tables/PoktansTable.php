<?php

namespace App\Filament\Resources\PoktanResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class PoktansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Poktan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gapoktan.name')
                    ->label('Gapoktan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('leader.name')
                    ->label('Ketua')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_members')
                    ->label('Anggota')
                    ->numeric()
                    ->getStateUsing(fn ($record) => $record->members()->count())
                    ->sortable(),
                TextColumn::make('total_land_area')
                    ->label('Luas Lahan')
                    ->numeric()
                    ->suffix(' Ha')
                    ->sortable(),
                TextColumn::make('established_date')
                    ->label('Tgl Berdiri')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        default => 'primary',
                    }),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('gapoktan_id')
                    ->label('Gapoktan')
                    ->relationship('gapoktan', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        'suspended' => 'Ditangguhkan',
                    ]),
                Filter::make('established_date')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('established_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('established_date', '<=', $date),
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
            ->defaultSort('established_date', 'desc');
    }
}