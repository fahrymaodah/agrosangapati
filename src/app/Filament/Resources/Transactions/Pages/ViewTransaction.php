<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->hidden(fn (Transaction $record): bool => $record->status !== 'pending'),
            
            Action::make('approve')
                ->label('Setujui Transaksi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Setujui Transaksi')
                ->modalDescription('Apakah Anda yakin ingin menyetujui transaksi ini? Saldo kas akan diperbarui sesuai dengan transaksi ini.')
                ->modalSubmitActionLabel('Ya, Setujui')
                ->visible(fn (Transaction $record): bool => 
                    $record->status === 'pending' && 
                    (Auth::user()->isKetuaPoktan() || Auth::user()->isKetuaGapoktan() || Auth::user()->isSuperadmin())
                )
                ->action(function (Transaction $record) {
                    $record->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->success()
                        ->title('Transaksi Disetujui')
                        ->body('Transaksi berhasil disetujui dan saldo kas telah diperbarui.')
                        ->send();
                    
                    $this->redirect(TransactionResource::getUrl('index'));
                }),
            
            Action::make('reject')
                ->label('Tolak Transaksi')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Tolak Transaksi')
                ->modalDescription('Apakah Anda yakin ingin menolak transaksi ini? Transaksi yang ditolak tidak akan mempengaruhi saldo kas.')
                ->modalSubmitActionLabel('Ya, Tolak')
                ->visible(fn (Transaction $record): bool => 
                    $record->status === 'pending' && 
                    (Auth::user()->isKetuaPoktan() || Auth::user()->isKetuaGapoktan() || Auth::user()->isSuperadmin())
                )
                ->action(function (Transaction $record) {
                    $record->update([
                        'status' => 'rejected',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->warning()
                        ->title('Transaksi Ditolak')
                        ->body('Transaksi berhasil ditolak.')
                        ->send();
                    
                    $this->redirect(TransactionResource::getUrl('index'));
                }),
        ];
    }
}

