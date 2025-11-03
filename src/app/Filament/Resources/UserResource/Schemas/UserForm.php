<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Forms\Get;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                                'admin' => 'Administrator',
                                'manager' => 'Manager',
                                'staff' => 'Staff',
                                'poktan_leader' => 'Ketua Poktan',
                                'member' => 'Anggota',
                            ])
                            ->required()
                            ->live(),
                        Select::make('poktan_id')
                            ->label('Poktan')
                            ->relationship('poktan', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => in_array($get('role'), ['poktan_leader', 'member'])),
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
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
                    ->collapsible(),
            ]);
    }
}