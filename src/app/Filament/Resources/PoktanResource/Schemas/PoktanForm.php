<?php

namespace App\Filament\Resources\PoktanResource\Schemas;

use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;

class PoktanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Poktan')
                    ->description('Data kelompok tani')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Poktan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Kode Poktan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(10),
                        Select::make('gapoktan_id')
                            ->label('Gapoktan')
                            ->relationship('gapoktan', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('leader_id')
                            ->label('Ketua Poktan')
                            ->relationship('leader', 'name')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('established_date')
                            ->label('Tanggal Berdiri')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Tidak Aktif',
                                'suspended' => 'Ditangguhkan',
                            ])
                            ->required()
                            ->default('active'),
                    ]),
                Section::make('Kontak & Lokasi')
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ]),
                Section::make('Informasi Lahan')
                    ->schema([
                        TextInput::make('total_land_area')
                            ->label('Luas Lahan (Ha)')
                            ->numeric()
                            ->suffix('Ha'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}