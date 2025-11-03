<?php

namespace App\Filament\Resources\ShipmentResource\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Forms\Form;

class ShipmentForm
{
    public static function schema(): Schema
    {
        return Schema::make([
            Section::make('Informasi Pengiriman')
                ->description('Data dasar pengiriman')
                ->schema([
                    TextInput::make('tracking_number')
                        ->label('Nomor Resi')
                        ->unique(ignoreRecord: true)
                        ->maxLength(100)
                        ->placeholder('Auto-generate jika kosong')
                        ->helperText('Akan dibuat otomatis jika tidak diisi'),

                    Select::make('order_id')
                        ->label('Order')
                        ->relationship('order', 'order_number')
                        ->required()
                        ->searchable()
                        ->preload(),

                    Select::make('courier')
                        ->label('Kurir')
                        ->options([
                            'jne' => 'JNE',
                            'jnt' => 'J&T Express',
                            'sicepat' => 'SiCepat',
                            'anteraja' => 'AnterAja',
                            'pos' => 'POS Indonesia',
                            'internal' => 'Internal/Sendiri',
                        ])
                        ->required()
                        ->searchable(),

                    Select::make('service_type')
                        ->label('Jenis Layanan')
                        ->options([
                            'regular' => 'Regular',
                            'express' => 'Express',
                            'same_day' => 'Same Day',
                            'cargo' => 'Cargo',
                        ])
                        ->required(),
                ])
                ->columns(2),

            Section::make('Detail Pengiriman')
                ->description('Informasi detail pengiriman')
                ->schema([
                    TextInput::make('weight')
                        ->label('Berat Total')
                        ->numeric()
                        ->required()
                        ->suffix('kg')
                        ->minValue(0),

                    TextInput::make('shipping_cost')
                        ->label('Biaya Kirim')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->minValue(0),

                    DateTimePicker::make('shipped_at')
                        ->label('Tanggal Kirim')
                        ->native(false),

                    DateTimePicker::make('estimated_arrival')
                        ->label('Estimasi Tiba')
                        ->native(false)
                        ->after('shipped_at'),

                    DateTimePicker::make('delivered_at')
                        ->label('Tanggal Diterima')
                        ->native(false)
                        ->after('shipped_at'),
                ])
                ->columns(2),

            Section::make('Alamat Pengiriman')
                ->description('Detail alamat tujuan')
                ->schema([
                    TextInput::make('recipient_name')
                        ->label('Nama Penerima')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('recipient_phone')
                        ->label('Telepon Penerima')
                        ->tel()
                        ->required()
                        ->maxLength(20),

                    Textarea::make('shipping_address')
                        ->label('Alamat Lengkap')
                        ->required()
                        ->rows(3)
                        ->maxLength(500),

                    TextInput::make('postal_code')
                        ->label('Kode Pos')
                        ->maxLength(10),
                ])
                ->columns(2),

            Section::make('Status & Bukti')
                ->description('Status pengiriman dan dokumentasi')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'processing' => 'Diproses',
                            'in_transit' => 'Dalam Pengiriman',
                            'delivered' => 'Terkirim',
                            'returned' => 'Dikembalikan',
                            'cancelled' => 'Dibatalkan',
                        ])
                        ->required()
                        ->default('pending'),

                    FileUpload::make('proof_of_delivery')
                        ->label('Bukti Terima')
                        ->image()
                        ->helperText('Upload foto bukti penerimaan barang'),

                    Textarea::make('delivery_notes')
                        ->label('Catatan Pengiriman')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Catatan tambahan tentang pengiriman'),
                ])
                ->columns(2),
        ]);
    }
}
