<?php

namespace App\Filament\Resources\NewHireResource\Pages;

use App\Filament\Resources\NewHireResource;
use App\Models\User;
use App\Notifications\NewHireRequestCreated;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateNewHire extends CreateRecord
{
    protected static string $resource = NewHireResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Solicitud creada exitosamente!')
            ->body('La solicitud de nuevo ingreso ha sido registrada y notificada al equipo de Sistemas.')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->duration(5000);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Obtener usuarios del departamento de Sistemas o Soporte TI
        $systemsUsers = User::whereHas('department', function ($query) {
            $query->whereIn('name', ['Sistemas', 'Soporte TI']);
        })->get();

        // Enviar notificaciones a todos los usuarios del equipo de Sistemas/Soporte TI
        foreach ($systemsUsers as $user) {
            $user->notify(new NewHireRequestCreated($this->record));
        }
    }
}
