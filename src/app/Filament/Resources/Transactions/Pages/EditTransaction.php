<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            
            Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Setujui Transaksi')
                ->modalDescription('Apakah Anda yakin ingin menyetujui transaksi ini?')
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
                        ->body('Transaksi berhasil disetujui.')
                        ->send();
                    
                    $this->redirect(TransactionResource::getUrl('view', ['record' => $record]));
                }),
            
            Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Tolak Transaksi')
                ->modalDescription('Apakah Anda yakin ingin menolak transaksi ini?')
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
                    
                    $this->redirect(TransactionResource::getUrl('view', ['record' => $record]));
                }),
            
            DeleteAction::make()
                ->hidden(fn (Transaction $record): bool => $record->status !== 'pending'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}

