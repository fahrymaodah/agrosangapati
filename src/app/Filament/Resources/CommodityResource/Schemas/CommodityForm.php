<?php

namespace App\Filament\Resources\CommodityResource\Schemas;

use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;

class CommodityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Komoditas')
                    ->description('Data dasar komoditas hasil bumi')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Komoditas')
                            ->required()
                            ->maxLength(255),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'pangan' => 'Pangan',
                                'hortikultura' => 'Hortikultura', 
                                'perkebunan' => 'Perkebunan',
                                'perikanan' => 'Perikanan',
                                'peternakan' => 'Peternakan',
                            ])
                            ->required(),
                        Select::make('unit')
                            ->label('Satuan')
                            ->options([
                                'kg' => 'Kilogram (kg)',
                                'ton' => 'Ton',
                                'kuintal' => 'Kuintal',
                                'karung' => 'Karung',
                                'ikat' => 'Ikat',
                                'ekor' => 'Ekor',
                                'liter' => 'Liter',
                            ])
                            ->required()
                            ->default('kg'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                Section::make('Detail Tambahan')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}