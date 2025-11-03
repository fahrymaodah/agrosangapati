<?php

namespace App\Filament\Resources\CashBalances\Pages;

use App\Filament\Resources\CashBalances\CashBalanceResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\CashBalance;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewCashBalance extends ViewRecord
{
    protected static string $resource = CashBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewTransactions')
                ->label('Lihat Semua Transaksi')
                ->icon('heroicon-o-list-bullet')
                ->color('info')
                ->url(fn (CashBalance $record): string => 
                    TransactionResource::getUrl('index', [
                        'tableFilters' => [
                            'poktan_id' => ['value' => $record->poktan_id],
                        ],
                    ])
                ),
            
            Action::make('viewPendingTransactions')
                ->label('Transaksi Menunggu')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->badge(fn (CashBalance $record): ?string => 
                    ($count = \App\Models\Transaction::where('poktan_id', $record->poktan_id)
                        ->where('status', 'pending')
                        ->count()) > 0 ? (string) $count : null
                )
                ->url(fn (CashBalance $record): string => 
                    TransactionResource::getUrl('index', [
                        'tableFilters' => [
                            'poktan_id' => ['value' => $record->poktan_id],
                            'status' => ['value' => 'pending'],
                        ],
                    ])
                )
                ->visible(fn (CashBalance $record): bool => 
                    \App\Models\Transaction::where('poktan_id', $record->poktan_id)
                        ->where('status', 'pending')
                        ->exists()
                ),
        ];
    }
}

