<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                // Kolom Kiri - Informasi Pengguna
                Section::make('Informasi Pengguna')
                    ->description('Data dasar pengguna sistem')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(20),
                        Select::make('role')
                            ->label('Peran')
                            ->options([
                                'superadmin' => 'Administrator',
                                'ketua_gapoktan' => 'Ketua Gapoktan',
                                'pengurus_gapoktan' => 'Pengurus Gapoktan',
                                'ketua_poktan' => 'Ketua Poktan',
                                'pengurus_poktan' => 'Pengurus Poktan',
                                'anggota_poktan' => 'Anggota Poktan',
                            ])
                            ->helperText('Pilih peran sesuai dengan tanggung jawab pengguna')
                            ->required()
                            ->native(false)
                            ->live(),
                        Select::make('poktan_id')
                            ->label('Poktan')
                            ->relationship('poktan', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(fn (Get $get) => in_array($get('role'), ['ketua_poktan', 'pengurus_poktan', 'anggota_poktan']))
                            ->visible(fn (Get $get) => in_array($get('role'), ['ketua_poktan', 'pengurus_poktan', 'anggota_poktan']))
                            ->helperText('Pilih poktan yang akan dikelola atau menjadi anggotanya'),
                        Toggle::make('is_active')
                            ->label('Status Akun')
                            ->helperText('Aktifkan untuk memungkinkan pengguna login ke sistem')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])
                    ->columnSpan(1),
                
                // Kolom Kanan - Keamanan & Informasi Tambahan
                Grid::make(1)
                    ->schema([
                        Section::make('Keamanan')
                            ->schema([
                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->minLength(8)
                                    ->same('passwordConfirmation')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
                                TextInput::make('passwordConfirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->minLength(8)
                                    ->dehydrated(false),
                            ])
                            ->hiddenOn('view'),
                        Section::make('Informasi Tambahan')
                            ->schema([
                                Textarea::make('address')
                                    ->label('Alamat')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->columnSpan(1),
            ]);
    }
}