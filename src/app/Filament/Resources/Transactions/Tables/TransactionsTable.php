<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Poktan;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('poktan.name')
                    ->label('Poktan')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-group')
                    ->description(fn (Transaction $record): string => $record->poktan->gapoktan->name ?? '-'),
                
                TextColumn::make('transaction_type')
                    ->label('Tipe')
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
                
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Transaction $record): string => $record->category->description ?? '-'),
                
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                            ->label('Total'),
                    ]),
                
                TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                ImageColumn::make('receipt_photo')
                    ->label('Bukti')
                    ->disk('public')
                    ->visibility('private')
                    ->size(40)
                    ->defaultImageUrl(url('/images/no-receipt.png')),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
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
                
                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('Belum disetujui')
                    ->toggleable(),
                
                TextColumn::make('approved_at')
                    ->label('Tanggal Persetujuan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum disetujui')
                    ->toggleable(),
                
                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('poktan_id')
                    ->label('Poktan')
                    ->relationship('poktan', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('transaction_type')
                    ->label('Tipe Transaksi')
                    ->options([
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                    ])
                    ->native(false),
                
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->native(false),
                
                Filter::make('transaction_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Dari: ' . \Carbon\Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Sampai: ' . \Carbon\Carbon::parse($data['until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                
                EditAction::make()
                    ->hidden(fn (Transaction $record): bool => $record->status !== 'pending'),
                
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
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('bulkApprove')
                        ->label('Setujui Semua')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Transaksi Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menyetujui semua transaksi yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Setujui Semua')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $approved = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'approved',
                                        'approved_by' => Auth::id(),
                                        'approved_at' => now(),
                                    ]);
                                    $approved++;
                                }
                            }
                            
                            Notification::make()
                                ->success()
                                ->title('Transaksi Disetujui')
                                ->body("{$approved} transaksi berhasil disetujui.")
                                ->send();
                        }),
                    
                    \Filament\Actions\BulkAction::make('bulkReject')
                        ->label('Tolak Semua')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Transaksi Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menolak semua transaksi yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Tolak Semua')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $rejected = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'rejected',
                                        'approved_by' => Auth::id(),
                                        'approved_at' => now(),
                                    ]);
                                    $rejected++;
                                }
                            }
                            
                            Notification::make()
                                ->warning()
                                ->title('Transaksi Ditolak')
                                ->body("{$rejected} transaksi berhasil ditolak.")
                                ->send();
                        }),
                    
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }
}
