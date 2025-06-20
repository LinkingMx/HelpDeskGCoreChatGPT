<?php

namespace App\Notifications;

use App\Models\NewHire;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewHireRequestCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected NewHire $newHire
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
            ->subject('Nueva Solicitud de Ingreso - '.$this->newHire->employee_name)
            ->greeting('¡Hola!')
            ->line('Se ha creado una nueva solicitud de ingreso que requiere tu atención.')
            ->line('**Detalles del nuevo empleado:**')
            ->line('• **Nombre:** '.$this->newHire->employee_name)
            ->line('• **Puesto:** '.$this->newHire->employee_position)
            ->line('• **Fecha de Ingreso:** '.$this->newHire->start_date->format('d/m/Y'))
            ->line('• **Sucursal/Área:** '.$this->newHire->client->name)
            ->line('• **Jefe Directo:** '.$this->newHire->direct_supervisor)
            ->line('• **Estado:** '.$this->newHire->status_badge)
            ->action('Ver Solicitud', url('/admin/nuevos-ingresos/'.$this->newHire->id))
            ->line('Por favor, revisa los equipos requeridos y procede con la configuración necesaria.')
            ->salutation('Saludos, Equipo de Recursos Humanos');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Nueva Solicitud de Ingreso',
            'body' => 'Nueva solicitud para '.$this->newHire->employee_name.' ('.$this->newHire->employee_position.')',
            'icon' => 'heroicon-o-user-plus',
            'color' => 'info',
            'actions' => [
                [
                    'label' => 'Ver Solicitud',
                    'url' => '/admin/nuevos-ingresos/'.$this->newHire->id,
                ],
            ],
            'new_hire_id' => $this->newHire->id,
            'employee_name' => $this->newHire->employee_name,
            'employee_position' => $this->newHire->employee_position,
            'start_date' => $this->newHire->start_date->format('d/m/Y'),
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
