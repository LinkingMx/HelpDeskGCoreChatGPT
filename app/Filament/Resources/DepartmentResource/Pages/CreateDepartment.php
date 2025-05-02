<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification; // Import Notification class

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

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
            ->title('Departamento Creado')
            ->body("El departamento '{$this->record->name}' ha sido creado exitosamente.");
    }
}
