<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord; // Import Notification class

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    protected static ?string $title = 'Crear Nuevo Departamento';

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
            ->title('🎉 ¡Departamento Creado Exitosamente!')
            ->body("El departamento '{$this->record->name}' ha sido configurado correctamente y ya está disponible para recibir y gestionar tickets de soporte.")
            ->duration(5000);
    }
}
