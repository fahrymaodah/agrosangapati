<?php

namespace App\Filament\Resources\UserResource\Tables;

use Filament\Forms;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->icon('heroicon-o-phone'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'ketua_gapoktan' => 'warning',
                        'pengurus_gapoktan' => 'info',
                        'ketua_poktan' => 'success',
                        'pengurus_poktan' => 'primary',
                        'anggota_poktan' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'superadmin' => 'Super Admin',
                        'ketua_gapoktan' => 'Ketua Gapoktan',
                        'pengurus_gapoktan' => 'Pengurus Gapoktan',
                        'ketua_poktan' => 'Ketua Poktan',
                        'pengurus_poktan' => 'Pengurus Poktan',
                        'anggota_poktan' => 'Anggota Poktan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('poktan.name')
                    ->label('Poktan')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-group')
                    ->default('-'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->email_verified_at !== null)
                    ->trueIcon(Heroicon::OutlinedCheckBadge)
                    ->falseIcon(Heroicon::OutlinedExclamationTriangle)
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'superadmin' => 'Super Admin',
                        'ketua_gapoktan' => 'Ketua Gapoktan',
                        'pengurus_gapoktan' => 'Pengurus Gapoktan',
                        'ketua_poktan' => 'Ketua Poktan',
                        'pengurus_poktan' => 'Pengurus Poktan',
                        'anggota_poktan' => 'Anggota Poktan',
                    ]),
                Tables\Filters\SelectFilter::make('poktan_id')
                    ->label('Poktan')
                    ->relationship('poktan', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ]),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->nullable(),
            ])
            ->bulkActions([
                Actions\BulkAction::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $record->update(['status' => 'active']);
                        });

                        Notification::make()
                            ->success()
                            ->title('Pengguna Diaktifkan')
                            ->body(count($records) . ' pengguna berhasil diaktifkan.')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Actions\BulkAction::make('deactivate')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $record->update(['status' => 'inactive']);
                        });

                        Notification::make()
                            ->success()
                            ->title('Pengguna Dinonaktifkan')
                            ->body(count($records) . ' pengguna berhasil dinonaktifkan.')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Actions\BulkAction::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('new_password')
                            ->label('Password Baru')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->same('password_confirmation'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $records->each(function ($record) use ($data) {
                            $record->update([
                                'password' => Hash::make($data['new_password']),
                            ]);
                        });

                        Notification::make()
                            ->success()
                            ->title('Password Direset')
                            ->body(count($records) . ' pengguna berhasil direset passwordnya.')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}