<?php

namespace App\Filament\Resources\GapoktanResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GapoktansTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('name')
                    ->label('Nama Gapoktan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('chairman')
                    ->label('Ketua')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('chairman_phone')
                    ->label('Telepon Ketua')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('regency')
                    ->label('Kabupaten/Kota')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_poktans')
                    ->label('Jml Poktan')
                    ->numeric(0)
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_members')
                    ->label('Total Anggota')
                    ->numeric(0)
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                TextColumn::make('total_land_area')
                    ->label('Luas Lahan')
                    ->numeric(2)
                    ->suffix(' ha')
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'suspended',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        'suspended' => 'Ditangguhkan',
                        default => $state,
                    }),

                TextColumn::make('established_date')
                    ->label('Tgl Berdiri')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'inactive' => 'Tidak Aktif',
                        'suspended' => 'Ditangguhkan',
                    ]),

                SelectFilter::make('regency')
                    ->label('Kabupaten/Kota')
                    ->options(function () {
                        return \App\Models\Gapoktan::query()
                            ->distinct()
                            ->pluck('regency', 'regency')
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name', 'asc');
    }
}
