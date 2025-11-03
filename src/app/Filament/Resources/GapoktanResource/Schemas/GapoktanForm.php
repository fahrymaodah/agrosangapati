<?php

namespace App\Filament\Resources\GapoktanResource\Schemas;

use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

class GapoktanForm
{
    public static function schema(): Schema
    {
        return Schema::make([
            Section::make('Informasi Gapoktan')
                ->description('Data dasar Gabungan Kelompok Tani')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Gapoktan')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Gapoktan Maju Bersama'),

                    TextInput::make('code')
                        ->label('Kode Gapoktan')
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->placeholder('Contoh: GPK-001'),

                    DatePicker::make('established_date')
                        ->label('Tanggal Berdiri')
                        ->required()
                        ->maxDate(now()),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'active' => 'Aktif',
                            'inactive' => 'Tidak Aktif',
                            'suspended' => 'Ditangguhkan',
                        ])
                        ->required()
                        ->default('active'),
                ])
                ->columns(2),

            Section::make('Kontak & Lokasi')
                ->description('Informasi kontak dan alamat')
                ->schema([
                    TextInput::make('chairman')
                        ->label('Ketua Gapoktan')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('chairman_phone')
                        ->label('Telepon Ketua')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('Telepon Kantor')
                        ->tel()
                        ->maxLength(20),

                    Textarea::make('address')
                        ->label('Alamat Lengkap')
                        ->required()
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),

                    TextInput::make('village')
                        ->label('Desa/Kelurahan')
                        ->maxLength(100),

                    TextInput::make('district')
                        ->label('Kecamatan')
                        ->maxLength(100),

                    TextInput::make('regency')
                        ->label('Kabupaten/Kota')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('province')
                        ->label('Provinsi')
                        ->required()
                        ->maxLength(100),
                ])
                ->columns(2),

            Section::make('Informasi Organisasi')
                ->description('Detail struktur organisasi')
                ->schema([
                    TextInput::make('total_poktans')
                        ->label('Jumlah Poktan Tergabung')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Akan otomatis terhitung dari data poktan'),

                    TextInput::make('total_members')
                        ->label('Total Anggota')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Akan otomatis terhitung dari semua poktan'),

                    TextInput::make('total_land_area')
                        ->label('Total Luas Lahan')
                        ->numeric()
                        ->suffix('ha')
                        ->minValue(0)
                        ->helperText('Akan otomatis terhitung dari semua poktan'),

                    TextInput::make('registration_number')
                        ->label('Nomor Registrasi')
                        ->maxLength(100)
                        ->placeholder('Nomor SK atau registrasi resmi'),
                ])
                ->columns(2),

            Section::make('Catatan')
                ->description('Informasi tambahan')
                ->schema([
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('Deskripsi singkat tentang gapoktan'),

                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Catatan tambahan'),
                ])
                ->columns(1)
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
