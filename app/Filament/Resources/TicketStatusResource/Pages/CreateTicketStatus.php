<?php

namespace App\Filament\Resources\TicketStatusResource\Pages;

use App\Filament\Resources\TicketStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification; // Import Notification class

class CreateTicketStatus extends CreateRecord
{
    protected static string $resource = TicketStatusResource::class;

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
            ->title('Estado de Ticket Creado')
            ->body("El estado de ticket '{$this->record->name}' ha sido creado exitosamente.");
    }
}
