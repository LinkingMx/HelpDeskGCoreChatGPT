<?php

namespace App\Filament\Resources\NewHireResource\Pages;

use App\Filament\Resources\NewHireResource;
use App\Models\User;
use App\Notifications\NewHireRequestUpdated;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditNewHire extends EditRecord
{
    protected static string $resource = NewHireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Solicitud actualizada!')
            ->body('Los cambios han sido guardados y notificados a los responsables.')
            ->icon('heroicon-o-pencil-square')
            ->color('success')
            ->duration(4000);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $currentUser = auth()->user();

        // Obtener usuarios del departamento de Sistemas o Soporte TI
        $systemsUsers = User::whereHas('department', function ($query) {
            $query->whereIn('name', ['Sistemas', 'Soporte TI']);
        })->get();

        // Obtener el usuario que creó la solicitud original
        $originalCreator = $this->record->createdBy;

        // Crear lista de usuarios únicos a notificar
        $usersToNotify = collect();

        // Agregar usuarios de Sistemas/Soporte TI
        $usersToNotify = $usersToNotify->merge($systemsUsers);

        // Agregar el usuario que creó la solicitud (si no está ya incluido)
        if ($originalCreator && ! $usersToNotify->contains('id', $originalCreator->id)) {
            $usersToNotify->push($originalCreator);
        }

        // Remover el usuario actual que está editando (para evitar auto-notificación)
        $usersToNotify = $usersToNotify->filter(function ($user) use ($currentUser) {
            return $user->id !== $currentUser->id;
        });

        // Enviar notificaciones
        foreach ($usersToNotify as $user) {
            $user->notify(new NewHireRequestUpdated($this->record, $currentUser));
        }
    }
}
