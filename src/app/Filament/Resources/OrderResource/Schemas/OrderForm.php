<?php

namespace App\Filament\Resources\OrderResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Product;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Pesanan')
                    ->description('Detail pesanan pelanggan')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Nomor Pesanan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),
                        Select::make('order_status')
                            ->label('Status Pesanan')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Terkirim',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('pending')
                            ->native(false),
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'unpaid' => 'Belum Dibayar',
                                'partial' => 'Dibayar Sebagian',
                                'paid' => 'Lunas',
                                'refunded' => 'Refund',
                            ])
                            ->required()
                            ->default('unpaid')
                            ->native(false),
                        Textarea::make('notes')
                            ->label('Catatan Pesanan')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Catatan tambahan untuk pesanan ini...'),
                    ])
                    ->columns(1),

                Section::make('Data Pelanggan')
                    ->description('Informasi kontak dan alamat pelanggan')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('buyer_name')
                            ->label('Nama Pelanggan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('buyer_phone')
                            ->label('Telepon Pelanggan')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        TextInput::make('buyer_email')
                            ->label('Email Pelanggan')
                            ->email()
                            ->maxLength(255),
                        Textarea::make('buyer_address')
                            ->label('Alamat Pelanggan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Item Pesanan')
                    ->description('Daftar produk yang dipesan')
                    ->columnSpan(2)
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateHydrated(function ($set, $get, ?string $state) {
                                        // Set product unit when form is loaded (for edit/view)
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('product_unit', $product->unit);
                                                $set('available_stock', $product->stock_quantity);
                                            }
                                        }
                                    })
                                    ->afterStateUpdated(function ($set, $get, ?string $state) {
                                        if (!$state) {
                                            return;
                                        }
                                        
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->price);
                                            $set('available_stock', $product->stock_quantity);
                                            $set('product_unit', $product->unit); // Set unit dari produk
                                            
                                            // Auto calculate subtotal
                                            $quantity = $get('quantity') ?? 1;
                                            $set('subtotal', $product->price * $quantity);
                                        }
                                    })
                                    ->columnSpan(4),

                                TextInput::make('available_stock')
                                    ->label('Stok Tersedia')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffix(fn ($get) => $get('product_unit') ?? 'unit')
                                    ->hiddenOn('view')
                                    ->columnSpan(2),
                                
                                Hidden::make('product_unit')
                                    ->dehydrated(false),

                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->suffix(fn ($get) => $get('product_unit') ?? 'unit')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($set, $get, ?string $state) {
                                        $unitPrice = floatval($get('unit_price') ?? 0);
                                        $quantity = floatval($state ?? 0);
                                        $subtotal = $unitPrice * $quantity;
                                        $set('subtotal', $subtotal);
                                        
                                        // Trigger parent repeater to recalculate total
                                        self::updateTotalAmount($set, $get);
                                    })
                                    ->columnSpan(2),

                                TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->required()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($set, $get, ?string $state) {
                                        $unitPrice = floatval($state ?? 0);
                                        $quantity = floatval($get('quantity') ?? 1);
                                        $subtotal = $unitPrice * $quantity;
                                        $set('subtotal', $subtotal);
                                        
                                        // Trigger parent repeater to recalculate total
                                        self::updateTotalAmount($set, $get);
                                    })
                                    ->columnSpan(2),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['product_id']) 
                                    ? Product::find($state['product_id'])?->name 
                                    : null
                            )
                            ->addActionLabel('Tambah Produk')
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Calculate total amount from all items
                                $totalAmount = 0;
                                if (is_array($state)) {
                                    foreach ($state as $item) {
                                        $totalAmount += floatval($item['subtotal'] ?? 0);
                                    }
                                }
                                
                                $set('total_amount', $totalAmount);
                                
                                // Recalculate grand total
                                $shippingCost = floatval($get('shipping_cost') ?? 0);
                                $set('grand_total', $totalAmount + $shippingCost);
                            }),
                    ]),

                Section::make('Ringkasan Pembayaran')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Total Harga')
                            ->numeric()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->live()
                            ->helperText('Total harga otomatis dihitung dari item pesanan'),

                        TextInput::make('shipping_cost')
                            ->label('Ongkos Kirim')
                            ->numeric()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('Rp')
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($set, $get, ?string $state) {
                                $totalAmount = floatval($get('total_amount') ?? 0);
                                $shippingCost = floatval($state ?? 0);
                                $set('grand_total', $totalAmount + $shippingCost);
                            }),

                        TextInput::make('grand_total')
                            ->label('Total Keseluruhan')
                            ->numeric()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->live()
                            ->extraAttributes(['class' => 'font-bold text-lg']),
                    ])
                    ->columns(3),
            ]);
    }
    
    protected static function updateTotalAmount($set, $get): void
    {
        // Get all items from repeater using relative path
        $items = $get('../../items') ?? [];
        
        $totalAmount = 0;
        if (is_array($items)) {
            foreach ($items as $item) {
                $totalAmount += floatval($item['subtotal'] ?? 0);
            }
        }
        
        // Update total_amount in parent form
        $set('../../total_amount', $totalAmount);
        
        // Recalculate grand total
        $shippingCost = floatval($get('../../shipping_cost') ?? 0);
        $set('../../grand_total', $totalAmount + $shippingCost);
    }
}