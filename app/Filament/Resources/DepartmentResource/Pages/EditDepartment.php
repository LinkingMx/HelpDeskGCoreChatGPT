<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord; // Import Notification class

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    protected static ?string $title = '✏️ Editar Departamento';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar Departamento')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('⚠️ Eliminar Departamento')
                ->modalDescription('¿Estás seguro de que quieres eliminar este departamento? 

⚠️ **ADVERTENCIA:** Esta acción eliminará permanentemente:
• El departamento y toda su configuración
• Las asignaciones de agentes a este departamento
• El historial de tickets asignados

Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar departamento')
                ->modalCancelActionLabel('Cancelar')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Departamento eliminado')
                        ->body('El departamento ha sido eliminado exitosamente.')
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
            ->title('✅ Departamento Actualizado')
            ->body("Los cambios en el departamento '{$this->record->name}' han sido guardados correctamente. La nueva configuración ya está activa.")
            ->duration(5000);
    }
}
