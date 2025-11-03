<?php

namespace App\Filament\Resources\OrderResource\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pesanan')
                    ->description('Detail pesanan pelanggan')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Nomor Pesanan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->maxLength(255),
                        DateTimePicker::make('order_date')
                            ->label('Tanggal Pesanan')
                            ->required()
                            ->default(now()),
                        DateTimePicker::make('delivery_date')
                            ->label('Tanggal Pengiriman')
                            ->required(),
                        Select::make('status')
                            ->label('Status Pesanan')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Dikonfirmasi',
                                'processing' => 'Diproses',
                                'ready' => 'Siap Kirim',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Terkirim',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('pending'),
                    ]),
                Section::make('Data Pelanggan')
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Nama Pelanggan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer_phone')
                            ->label('Telepon Pelanggan')
                            ->tel()
                            ->maxLength(20),
                        Textarea::make('customer_address')
                            ->label('Alamat Pelanggan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Pembayaran')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Total Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('discount_amount')
                            ->label('Diskon')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'pending' => 'Belum Dibayar',
                                'partial' => 'Dibayar Sebagian',
                                'paid' => 'Lunas',
                                'refunded' => 'Refund',
                            ])
                            ->required()
                            ->default('pending'),
                    ]),
                Section::make('Catatan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan Pesanan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}