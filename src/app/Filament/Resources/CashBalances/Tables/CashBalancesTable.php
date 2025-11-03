<?php

namespace App\Filament\Resources\CashBalances\Tables;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\CashBalance;
use App\Models\Poktan;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashBalancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('poktan.name')
                    ->label('Nama Poktan')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-group')
                    ->description(fn (CashBalance $record): string => $record->poktan->gapoktan->name ?? '-'),
                
                TextColumn::make('poktan.gapoktan.name')
                    ->label('Gapoktan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable()
                    ->size('lg')
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
                    ->description(fn (CashBalance $record): string => match (true) {
                        $record->balance < 0 => 'Saldo Negatif',
                        $record->balance == 0 => 'Saldo Kosong',
                        $record->balance < 1000000 => 'Saldo Rendah',
                        $record->balance < 10000000 => 'Saldo Sedang',
                        default => 'Saldo Tinggi',
                    }),
                
                TextColumn::make('last_updated')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->description(fn (CashBalance $record): string => 
                        $record->last_updated ? $record->last_updated->diffForHumans() : '-'
                    )
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
                
                SelectFilter::make('gapoktan_id')
                    ->label('Gapoktan')
                    ->options(fn () => \App\Models\Gapoktan::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }
                        return $query->whereHas('poktan.gapoktan', function (Builder $query) use ($data) {
                            $query->where('id', $data['value']);
                        });
                    })
                    ->searchable()
                    ->preload(),
                
                Filter::make('balance')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->label('Status Saldo')
                            ->options([
                                'negative' => 'Negatif',
                                'zero' => 'Kosong',
                                'low' => 'Rendah (< Rp 1jt)',
                                'medium' => 'Sedang (Rp 1jt - 10jt)',
                                'high' => 'Tinggi (> Rp 10jt)',
                            ])
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['status'] ?? null,
                            function (Builder $query, string $status): Builder {
                                return match ($status) {
                                    'negative' => $query->where('balance', '<', 0),
                                    'zero' => $query->where('balance', '=', 0),
                                    'low' => $query->whereBetween('balance', [0.01, 999999.99]),
                                    'medium' => $query->whereBetween('balance', [1000000, 9999999.99]),
                                    'high' => $query->where('balance', '>=', 10000000),
                                    default => $query,
                                };
                            }
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['status']) {
                            return null;
                        }
                        
                        return 'Saldo: ' . match ($data['status']) {
                            'negative' => 'Negatif',
                            'zero' => 'Kosong',
                            'low' => 'Rendah',
                            'medium' => 'Sedang',
                            'high' => 'Tinggi',
                            default => '',
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                
                Action::make('viewTransactions')
                    ->label('Lihat Transaksi')
                    ->icon('heroicon-o-list-bullet')
                    ->color('info')
                    ->url(fn (CashBalance $record): string => 
                        TransactionResource::getUrl('index', [
                            'tableFilters' => [
                                'poktan_id' => ['value' => $record->poktan_id],
                            ],
                        ])
                    ),
            ])
            ->defaultSort('balance', 'desc')
            ->poll('30s') // Auto-refresh every 30 seconds
            ->emptyStateHeading('Belum Ada Data Saldo Kas')
            ->emptyStateDescription('Data saldo kas akan muncul setelah ada transaksi yang disetujui.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}

