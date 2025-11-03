<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Hidden;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Forms\Get;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->description('Data dasar transaksi keuangan')
                    ->schema([
                        Select::make('poktan_id')
                            ->label('Poktan')
                            ->relationship('poktan', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih poktan yang melakukan transaksi'),

                        Select::make('transaction_type')
                            ->label('Jenis Transaksi')
                            ->options([
                                'income' => 'Pemasukan',
                                'expense' => 'Pengeluaran',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('category_id', null)),

                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Get $get, $query) => $query
                                    ->where('type', $get('transaction_type'))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Kategori akan difilter berdasarkan jenis transaksi'),

                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->step(1000)
                            ->placeholder('0')
                            ->helperText('Masukkan jumlah dalam Rupiah'),

                        DatePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now()),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Jelaskan detail transaksi ini...')
                            ->columnSpanFull(),

                        FileUpload::make('receipt_photo')
                            ->label('Foto Bukti')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120)
                            ->directory('receipts')
                            ->visibility('private')
                            ->helperText('Upload foto bukti transaksi (max 5MB)')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Status & Approval')
                    ->description('Informasi persetujuan transaksi')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false)
                            ->disabled(fn ($context) => $context === 'create')
                            ->helperText('Status akan otomatis "Pending" untuk transaksi baru'),

                        Hidden::make('created_by')
                            ->default(Auth::id()),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($context) => $context === 'create'),
            ]);
    }
}
