<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord; // Import Notification class

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Registrar Nuevo Cliente';

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    // Add redirect URL method
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Add created notification method
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-o-check-circle')
            ->title('🎉 ¡Cliente Registrado Exitosamente!')
            ->body("El cliente '{$this->record->name}' ha sido registrado correctamente y ya puede comenzar a utilizar el sistema de soporte.")
            ->duration(5000);
    }
}
