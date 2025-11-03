<?php

namespace App\Filament\Resources\StockResource\Schemas;

use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Forms\Form;

class StockForm
{
    public static function schema(): Schema
    {
        return Schema::make([
            Section::make('Informasi Stok')
                ->description('Informasi dasar stok barang')
                ->schema([
                    Select::make('commodity_id')
                        ->label('Komoditas')
                        ->relationship('commodity', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    Select::make('poktan_id')
                        ->label('Poktan')
                        ->relationship('poktan', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->suffix('kg')
                        ->default(0),

                    TextInput::make('location')
                        ->label('Lokasi Penyimpanan')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Gudang A, Rak 1'),
                ])
                ->columns(2),

            Section::make('Informasi Tambahan')
                ->description('Detail kualitas dan status stok')
                ->schema([
                    Select::make('quality_grade')
                        ->label('Grade Kualitas')
                        ->options([
                            'A' => 'Grade A (Premium)',
                            'B' => 'Grade B (Standar)',
                            'C' => 'Grade C (Ekonomis)',
                        ])
                        ->required()
                        ->default('B'),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'active' => 'Aktif',
                            'reserved' => 'Direservasi',
                            'sold' => 'Terjual',
                            'damaged' => 'Rusak',
                        ])
                        ->required()
                        ->default('active'),

                    DatePicker::make('harvest_date')
                        ->label('Tanggal Panen')
                        ->required()
                        ->maxDate(now()),

                    TextInput::make('min_stock_alert')
                        ->label('Alert Stok Minimum')
                        ->numeric()
                        ->minValue(0)
                        ->suffix('kg')
                        ->helperText('Sistem akan memberi peringatan jika stok di bawah nilai ini')
                        ->default(10),
                ])
                ->columns(2),

            Section::make('Catatan')
                ->description('Informasi tambahan tentang stok')
                ->schema([
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Catatan tambahan tentang kondisi atau informasi stok'),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
