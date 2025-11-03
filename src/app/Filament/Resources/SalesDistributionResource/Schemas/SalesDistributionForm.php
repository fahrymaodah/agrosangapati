<?php

namespace App\Filament\Resources\SalesDistributionResource\Schemas;

use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Forms\Form;

class SalesDistributionForm
{
    public static function schema(): Schema
    {
        return Schema::make([
            Section::make('Informasi Distribusi')
                ->description('Data dasar distribusi penjualan')
                ->schema([
                    Select::make('order_id')
                        ->label('Order')
                        ->relationship('order', 'order_number')
                        ->required()
                        ->searchable()
                        ->preload(),

                    Select::make('poktan_id')
                        ->label('Poktan')
                        ->relationship('poktan', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    DatePicker::make('distribution_date')
                        ->label('Tanggal Distribusi')
                        ->required()
                        ->default(now()),

                    TextInput::make('reference_number')
                        ->label('Nomor Referensi')
                        ->unique(ignoreRecord: true)
                        ->maxLength(100)
                        ->placeholder('Auto-generate jika kosong'),
                ])
                ->columns(2),

            Section::make('Pembagian Pendapatan')
                ->description('Detail pembagian hasil penjualan')
                ->schema([
                    TextInput::make('total_sales')
                        ->label('Total Penjualan')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->minValue(0),

                    TextInput::make('farmer_share_percentage')
                        ->label('Persentase Petani (%)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->default(70)
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            $totalSales = $get('total_sales') ?? 0;
                            $farmerShare = ($totalSales * $state) / 100;
                            $set('farmer_share_amount', $farmerShare);
                            
                            $organizationShare = $totalSales - $farmerShare;
                            $set('organization_share_amount', $organizationShare);
                        }),

                    TextInput::make('farmer_share_amount')
                        ->label('Bagian Petani')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->readOnly(),

                    TextInput::make('organization_share_amount')
                        ->label('Bagian Organisasi')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->readOnly(),
                ])
                ->columns(2),

            Section::make('Informasi Pembayaran')
                ->description('Status dan detail pembayaran')
                ->schema([
                    Select::make('payment_status')
                        ->label('Status Pembayaran')
                        ->options([
                            'pending' => 'Pending',
                            'processing' => 'Diproses',
                            'paid' => 'Dibayar',
                            'cancelled' => 'Dibatalkan',
                        ])
                        ->required()
                        ->default('pending'),

                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Tunai',
                            'transfer' => 'Transfer Bank',
                            'check' => 'Cek',
                            'other' => 'Lainnya',
                        ]),

                    DatePicker::make('payment_date')
                        ->label('Tanggal Pembayaran')
                        ->maxDate(now()),

                    TextInput::make('payment_reference')
                        ->label('Referensi Pembayaran')
                        ->maxLength(255)
                        ->placeholder('Nomor transfer, nomor cek, dll'),
                ])
                ->columns(2),

            Section::make('Catatan')
                ->description('Informasi tambahan')
                ->schema([
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Catatan tambahan tentang distribusi'),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
