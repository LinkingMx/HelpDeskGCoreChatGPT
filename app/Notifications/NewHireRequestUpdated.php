<?php

namespace App\Notifications;

use App\Models\NewHire;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewHireRequestUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected NewHire $newHire,
        protected User $updatedBy
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Solicitud de Ingreso Actualizada - '.$this->newHire->employee_name)
            ->greeting('¡Hola!')
            ->line('Se ha actualizado una solicitud de ingreso.')
            ->line('**Detalles del empleado:**')
            ->line('• **Nombre:** '.$this->newHire->employee_name)
            ->line('• **Puesto:** '.$this->newHire->employee_position)
            ->line('• **Fecha de Ingreso:** '.$this->newHire->start_date->format('d/m/Y'))
            ->line('• **Sucursal/Área:** '.$this->newHire->client->name)
            ->line('• **Estado:** '.$this->newHire->status_badge)
            ->line('**Actualizado por:** '.$this->updatedBy->name)
            ->action('Ver Solicitud', url('/admin/nuevos-ingresos/'.$this->newHire->id))
            ->line('Por favor, revisa los cambios realizados.')
            ->salutation('Saludos, Equipo de Recursos Humanos');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Solicitud Actualizada',
            'body' => 'Se actualizó la solicitud de '.$this->newHire->employee_name.' por '.$this->updatedBy->name,
            'icon' => 'heroicon-o-pencil-square',
            'color' => 'warning',
            'actions' => [
                [
                    'label' => 'Ver Cambios',
                    'url' => '/admin/nuevos-ingresos/'.$this->newHire->id,
                ],
            ],
            'new_hire_id' => $this->newHire->id,
            'employee_name' => $this->newHire->employee_name,
            'updated_by' => $this->updatedBy->name,
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
