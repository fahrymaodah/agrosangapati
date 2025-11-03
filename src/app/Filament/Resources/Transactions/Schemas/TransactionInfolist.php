<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Transaction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                    // Left Side: Main Transaction Information
                    Section::make('Informasi Transaksi')
                        ->schema([
                            TextEntry::make('poktan.name')
                                ->label('Nama Poktan')
                                ->icon('heroicon-o-user-group')
                                ->helperText(fn (Transaction $record): string => $record->poktan->gapoktan->name ?? '-'),
                            
                            TextEntry::make('transaction_type')
                                ->label('Tipe Transaksi')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'income' => 'success',
                                    'expense' => 'danger',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'income' => 'Pemasukan',
                                    'expense' => 'Pengeluaran',
                                    default => $state,
                                }),
                            
                            TextEntry::make('category.name')
                                ->label('Kategori')
                                ->icon('heroicon-o-tag')
                                ->helperText(fn (Transaction $record): ?string => $record->category->description),
                            
                            TextEntry::make('amount')
                                ->label('Jumlah')
                                ->money('IDR')
                                ->size('lg')
                                ->weight('bold')
                                ->color(fn (Transaction $record): string => 
                                    $record->transaction_type === 'income' ? 'success' : 'danger'
                                ),
                            
                            TextEntry::make('transaction_date')
                                ->label('Tanggal Transaksi')
                                ->date('d M Y')
                                ->icon('heroicon-o-calendar'),
                            
                            TextEntry::make('description')
                                ->label('Deskripsi')
                                ->placeholder('Tidak ada deskripsi')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->grow(),
                    
                    // Right Side: Receipt Photo
                    Section::make('Bukti Transaksi')
                        ->schema([
                            ImageEntry::make('receipt_photo')
                                ->label('')
                                ->disk('public')
                                ->visibility('private')
                                ->defaultImageUrl(url('/images/no-receipt.png'))
                                ->height(300)
                                ->extraImgAttributes(['class' => 'rounded-lg']),
                        ]),
                ]),
                
                // Status and Approval Section
                Section::make('Status & Persetujuan')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->size('lg')
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => $state,
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'pending' => 'heroicon-o-clock',
                                'approved' => 'heroicon-o-check-circle',
                                'rejected' => 'heroicon-o-x-circle',
                                default => 'heroicon-o-question-mark-circle',
                            }),
                        
                        TextEntry::make('approver.name')
                            ->label('Disetujui Oleh')
                            ->placeholder('Belum ada persetujuan')
                            ->icon('heroicon-o-user')
                            ->visible(fn (Transaction $record): bool => $record->status !== 'pending'),
                        
                        TextEntry::make('approved_at')
                            ->label('Tanggal Persetujuan')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum ada persetujuan')
                            ->icon('heroicon-o-clock')
                            ->visible(fn (Transaction $record): bool => $record->status !== 'pending'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(fn (Transaction $record): bool => $record->status === 'pending'),
                
                // Audit Trail Section
                Section::make('Riwayat')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Dibuat Oleh')
                            ->icon('heroicon-o-user'),
                        
                        TextEntry::make('created_at')
                            ->label('Tanggal Dibuat')
                            ->dateTime('d M Y H:i')
                            ->icon('heroicon-o-clock'),
                        
                        TextEntry::make('updated_at')
                            ->label('Terakhir Diubah')
                            ->dateTime('d M Y H:i')
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}

