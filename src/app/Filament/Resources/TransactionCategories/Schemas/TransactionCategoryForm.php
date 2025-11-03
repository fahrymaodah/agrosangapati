<?php

namespace App\Filament\Resources\TransactionCategories\Schemas;

use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;

class TransactionCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kategori')
                    ->description('Data dasar kategori transaksi')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Penjualan Hasil Panen'),

                        Select::make('type')
                            ->label('Jenis Transaksi')
                            ->options([
                                'income' => 'Pemasukan',
                                'expense' => 'Pengeluaran',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('Pilih jenis transaksi untuk kategori ini'),

                        Select::make('poktan_id')
                            ->label('Poktan')
                            ->relationship('poktan', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Kosongkan jika kategori berlaku untuk semua poktan'),

                        Toggle::make('is_default')
                            ->label('Kategori Default')
                            ->helperText('Kategori default tidak dapat dihapus')
                            ->default(false),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Opsional: Jelaskan kategori ini...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
