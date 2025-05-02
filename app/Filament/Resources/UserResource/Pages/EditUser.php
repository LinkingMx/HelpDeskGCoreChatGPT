<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification; // Import Notification class

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Add redirect URL method
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Add saved notification method
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-o-check-circle')
            ->title('Usuario Actualizado')
            ->body("El usuario '{$this->record->name}' ha sido actualizado exitosamente.");
    }
}
