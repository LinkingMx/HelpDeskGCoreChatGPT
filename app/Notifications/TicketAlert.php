<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $title,
        public string $body
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from('notificaciones@grupocosteno.com', 'GCore HelpDesk - Grupo Costeño')
            ->subject($this->title)
            ->greeting('¡Hola '.$notifiable->name.'!')
            ->line($this->body)
            ->line('**Detalles del Ticket:**')
            ->line('• **ID:** #'.$this->ticket->id)
            ->line('• **Asunto:** '.$this->ticket->subject)
            ->line('• **Cliente:** '.$this->ticket->client->name)
            ->line('• **Departamento:** '.$this->ticket->department->name)
            ->line('• **Estado:** '.$this->ticket->status->name)
            ->line('• **Prioridad:** '.match ($this->ticket->priority) {
                1 => 'Alta',
                2 => 'Media',
                3 => 'Baja',
                default => 'Media'
            })
            ->action('Ver Ticket Completo', url("/admin/tickets/{$this->ticket->id}"))
            ->line('Gracias por usar GCore HelpDesk.')
            ->salutation('Saludos cordiales,')
            ->salutation('El Equipo de Soporte de Grupo Costeño');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'actions' => [
                [
                    'name' => 'view',
                    'color' => null,
                    'event' => null,
                    'eventData' => [],
                    'dispatchDirection' => false,
                    'dispatchToComponent' => null,
                    'extraAttributes' => [],
                    'icon' => null,
                    'iconPosition' => 'before',
                    'iconSize' => null,
                    'isOutlined' => false,
                    'isDisabled' => false,
                    'label' => 'Ver Ticket',
                    'shouldClose' => false,
                    'shouldMarkAsRead' => false,
                    'shouldMarkAsUnread' => false,
                    'shouldOpenUrlInNewTab' => false,
                    'size' => 'sm',
                    'tooltip' => null,
                    'url' => url("/admin/tickets/{$this->ticket->id}"),
                    'view' => 'filament-actions::button-action',
                ],
            ],
            'body' => $this->body,
            'color' => 'info',
            'duration' => 'persistent',
            'icon' => 'heroicon-o-ticket',
            'iconColor' => null,
            'status' => null,
            'title' => $this->title,
            'view' => 'filament-notifications::notification',
            'viewData' => [],
            'format' => 'filament',
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->title,
            'body' => $this->body,
            'url' => url("/admin/tickets/{$this->ticket->id}"),
        ];
    }
}
