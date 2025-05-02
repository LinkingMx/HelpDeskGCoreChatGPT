<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification; // Import Notification class

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

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
            ->title('Cliente Creado')
            ->body("El cliente '{$this->record->name}' ha sido creado exitosamente.");
    }
}
