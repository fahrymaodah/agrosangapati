<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Hash;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert status enum to is_active checkbox for display
        $data['is_active'] = ($data['status'] ?? 'active') === 'active';
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            
            Action::make('toggleStatus')
                ->label(fn (User $record) => $record->status === 'active' ? 'Nonaktifkan' : 'Aktifkan')
                ->icon(fn (User $record) => $record->status === 'active' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn (User $record) => $record->status === 'active' ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function (User $record) {
                    $record->update(['status' => $record->status === 'active' ? 'inactive' : 'active']);
                    
                    Notification::make()
                        ->success()
                        ->title('Status Diperbarui')
                        ->body('Status pengguna berhasil diubah.')
                        ->send();
                })
                ->after(fn () => $this->refreshFormData(['is_active'])),
            
            Action::make('resetPassword')
                ->label('Reset Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->form([
                    TextInput::make('new_password')
                        ->label('Password Baru')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->same('password_confirmation'),
                    TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password')
                        ->password()
                        ->required()
                        ->minLength(8),
                ])
                ->action(function (User $record, array $data) {
                    $record->update([
                        'password' => Hash::make($data['new_password']),
                    ]);
                    
                    Notification::make()
                        ->success()
                        ->title('Password Direset')
                        ->body('Password berhasil diperbarui.')
                        ->send();
                }),
        ];
    }
}