<?php

namespace App\Filament\Resources\HarvestResource\Schemas;

use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;

class HarvestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Panen')
                    ->description('Data hasil panen petani')
                    ->schema([
                        Select::make('poktan_id')
                            ->label('Poktan')
                            ->relationship('poktan', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('commodity_id')
                            ->label('Komoditas')
                            ->relationship('commodity', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('farmer_name')
                            ->label('Nama Petani')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('harvest_date')
                            ->label('Tanggal Panen')
                            ->required()
                            ->default(now()),
                    ]),
                Section::make('Detail Panen')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Jumlah Panen')
                            ->required()
                            ->numeric()
                            ->suffix('kg'),
                        Select::make('quality_grade')
                            ->label('Kualitas')
                            ->options([
                                'A' => 'Grade A (Premium)',
                                'B' => 'Grade B (Baik)',
                                'C' => 'Grade C (Standar)',
                            ])
                            ->required(),
                        TextInput::make('price_per_kg')
                            ->label('Harga per Kg')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                    ]),
                Section::make('Catatan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}