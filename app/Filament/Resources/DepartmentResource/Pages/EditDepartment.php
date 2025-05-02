<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification; // Import Notification class

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

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
            ->title('Departamento Actualizado')
            ->body("El departamento '{$this->record->name}' ha sido actualizado exitosamente.");
    }
}

