<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord; // Import Notification class

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Editar Cliente';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar Cliente')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('⚠️ Eliminar Cliente')
                ->modalDescription('¿Estás seguro de que quieres eliminar este cliente? 

⚠️ **ADVERTENCIA:** Esta acción eliminará permanentemente:
• El cliente y toda su información
• Todos los usuarios asociados a este cliente
• El historial completo de tickets del cliente
• Todas las conversaciones y archivos adjuntos

Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar cliente')
                ->modalCancelActionLabel('Cancelar')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Cliente eliminado')
                        ->body('El cliente ha sido eliminado exitosamente.')
                ),
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
            ->title('✅ Cliente Actualizado')
            ->body("Los cambios en el cliente '{$this->record->name}' han sido guardados correctamente. La nueva información ya está disponible en el sistema.")
            ->duration(5000);
    }
}
