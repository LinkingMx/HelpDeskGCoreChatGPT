<?php

namespace App\Filament\Resources\TicketStatusResource\Pages;

use App\Filament\Resources\TicketStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification; // Import Notification class

class EditTicketStatus extends EditRecord
{
    protected static string $resource = TicketStatusResource::class;

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
            ->title('Estado de Ticket Actualizado')
            ->body("El estado de ticket '{$this->record->name}' ha sido actualizado exitosamente.");
    }
}
