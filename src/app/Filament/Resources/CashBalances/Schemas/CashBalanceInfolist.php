<?php

namespace App\Filament\Resources\CashBalances\Schemas;

use App\Models\CashBalance;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;

class CashBalanceInfolist
{
    public static function schema(): array
    {
        return [
            Section::make('Informasi Poktan')
                ->schema([
                    TextEntry::make('poktan.name')
                        ->label('Nama Poktan')
                            ->icon('heroicon-o-user-group')
                            ->size('lg')
                            ->weight('bold'),
                        
                        TextEntry::make('poktan.gapoktan.name')
                            ->label('Gapoktan')
                            ->icon('heroicon-o-building-office-2'),
                        
                        TextEntry::make('poktan.chairman.name')
                            ->label('Ketua Poktan')
                            ->icon('heroicon-o-user')
                            ->placeholder('Belum ada ketua'),
                        
                        TextEntry::make('poktan.members_count')
                            ->label('Jumlah Anggota')
                            ->icon('heroicon-o-users')
                            ->suffix(' anggota')
                            ->placeholder('0'),
                    ])
                    ->columns(2),
                
                Section::make('Informasi Saldo')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('balance')
                                    ->label('Saldo Saat Ini')
                                    ->money('IDR')
                                    ->size('xl')
                                    ->weight('bold')
                                    ->color(fn (CashBalance $record): string => match (true) {
                                        $record->balance < 0 => 'danger',
                                        $record->balance == 0 => 'warning',
                                        $record->balance < 1000000 => 'info',
                                        default => 'success',
                                    })
                                    ->icon(fn (CashBalance $record): string => match (true) {
                                        $record->balance < 0 => 'heroicon-o-arrow-trending-down',
                                        $record->balance == 0 => 'heroicon-o-minus',
                                        default => 'heroicon-o-arrow-trending-up',
                                    })
                                    ->columnSpan(3),
                            ]),
                        
                        TextEntry::make('last_updated')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d M Y H:i')
                            ->icon('heroicon-o-clock')
                            ->helperText(fn (CashBalance $record): string => 
                                $record->last_updated ? $record->last_updated->diffForHumans() : '-'
                            ),
                        
                        TextEntry::make('status')
                            ->label('Status Saldo')
                            ->badge()
                            ->color(fn (CashBalance $record): string => match (true) {
                                $record->balance < 0 => 'danger',
                                $record->balance == 0 => 'warning',
                                $record->balance < 1000000 => 'info',
                                default => 'success',
                            })
                            ->formatStateUsing(fn (CashBalance $record): string => match (true) {
                                $record->balance < 0 => 'Saldo Negatif',
                                $record->balance == 0 => 'Saldo Kosong',
                                $record->balance < 1000000 => 'Saldo Rendah',
                                $record->balance < 10000000 => 'Saldo Sedang',
                                default => 'Saldo Tinggi',
                            }),
                    ])
                    ->columns(2),
                
                Section::make('Riwayat')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i')
                            ->icon('heroicon-o-clock'),
                        
                        TextEntry::make('updated_at')
                            ->label('Terakhir Diubah')
                            ->dateTime('d M Y H:i')
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ];
    }
}

