<?php

namespace App\Filament\Resources\StockMovementResource\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Forms\Form;

class StockMovementForm
{
    public static function schema(): Schema
    {
        return Schema::make([
            Section::make('Informasi Pergerakan')
                ->description('Detail pergerakan stok barang')
                ->schema([
                    Select::make('stock_id')
                        ->label('Stok')
                        ->relationship('stock', 'id')
                        ->disabled()
                        ->searchable()
                        ->preload(),

                    Select::make('movement_type')
                        ->label('Jenis Pergerakan')
                        ->options([
                            'in' => 'Masuk',
                            'out' => 'Keluar',
                            'transfer' => 'Transfer',
                            'adjustment' => 'Penyesuaian',
                            'damage' => 'Kerusakan',
                        ])
                        ->disabled(),

                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
                        ->suffix('kg')
                        ->disabled(),

                    DateTimePicker::make('created_at')
                        ->label('Tanggal Pergerakan')
                        ->disabled(),
                ])
                ->columns(2),

            Section::make('Referensi')
                ->description('Informasi referensi terkait')
                ->schema([
                    TextInput::make('reference_type')
                        ->label('Tipe Referensi')
                        ->disabled(),

                    TextInput::make('reference_id')
                        ->label('ID Referensi')
                        ->disabled(),

                    TextInput::make('from_location')
                        ->label('Dari Lokasi')
                        ->disabled(),

                    TextInput::make('to_location')
                        ->label('Ke Lokasi')
                        ->disabled(),
                ])
                ->columns(2),

            Section::make('User & Catatan')
                ->description('Informasi user dan catatan')
                ->schema([
                    Select::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name')
                        ->disabled(),

                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->disabled(),
                ])
                ->columns(1),
        ]);
    }
}
