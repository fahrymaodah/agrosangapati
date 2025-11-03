<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Forms\Form;

class ProductForm
{
    public static function schema(): Schema
    {
        return Schema::make([
            Section::make('Informasi Produk')
                ->description('Informasi dasar produk olahan')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Produk')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Beras Organik Premium'),

                    TextInput::make('sku')
                        ->label('SKU / Kode Produk')
                        ->unique(ignoreRecord: true)
                        ->maxLength(100)
                        ->placeholder('Contoh: PRD-001'),

                    Select::make('commodity_id')
                        ->label('Komoditas Dasar')
                        ->relationship('commodity', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    Select::make('category')
                        ->label('Kategori')
                        ->options([
                            'raw' => 'Mentah',
                            'processed' => 'Olahan',
                            'packaged' => 'Kemasan',
                            'organic' => 'Organik',
                        ])
                        ->required()
                        ->default('processed'),
                ])
                ->columns(2),

            Section::make('Harga & Stok')
                ->description('Informasi harga dan ketersediaan')
                ->schema([
                    TextInput::make('price')
                        ->label('Harga Jual')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->minValue(0),

                    TextInput::make('cost')
                        ->label('Harga Modal')
                        ->numeric()
                        ->prefix('Rp')
                        ->minValue(0)
                        ->helperText('Harga pokok produk'),

                    TextInput::make('stock_quantity')
                        ->label('Stok Tersedia')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->suffix('unit'),

                    TextInput::make('min_order_quantity')
                        ->label('Minimal Order')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->suffix('unit'),

                    TextInput::make('unit')
                        ->label('Satuan')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('kg, pack, box, dll'),

                    TextInput::make('weight')
                        ->label('Berat per Unit')
                        ->numeric()
                        ->suffix('gram')
                        ->helperText('Untuk perhitungan ongkir'),
                ])
                ->columns(2),

            Section::make('Deskripsi & Gambar')
                ->description('Informasi detail dan visual produk')
                ->schema([
                    RichEditor::make('description')
                        ->label('Deskripsi Produk')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'bulletList',
                            'orderedList',
                        ])
                        ->columnSpanFull(),

                    FileUpload::make('images')
                        ->label('Gambar Produk')
                        ->image()
                        ->multiple()
                        ->maxFiles(5)
                        ->reorderable()
                        ->helperText('Upload maksimal 5 gambar produk')
                        ->columnSpanFull(),

                    Textarea::make('specifications')
                        ->label('Spesifikasi')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('Detail spesifikasi produk'),

                    Toggle::make('is_active')
                        ->label('Status Aktif')
                        ->default(true)
                        ->helperText('Produk aktif akan ditampilkan di katalog'),
                ])
                ->columns(2),

            Section::make('SEO & Marketing')
                ->description('Informasi untuk pemasaran online')
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta Title')
                        ->maxLength(255),

                    Textarea::make('meta_description')
                        ->label('Meta Description')
                        ->rows(2)
                        ->maxLength(500),

                    Textarea::make('tags')
                        ->label('Tags')
                        ->rows(2)
                        ->placeholder('Pisahkan dengan koma. Contoh: organik, sehat, premium')
                        ->helperText('Tag untuk memudahkan pencarian'),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
