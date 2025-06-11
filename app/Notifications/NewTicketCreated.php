<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewTicketCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Ticket $ticket)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo ticket: ' . $this->ticket->subject)
            ->greeting('Hola ' . $notifiable->name)
            ->line('Se ha creado un nuevo ticket en tu departamento.')
            ->line('Ticket #' . $this->ticket->id . ': ' . $this->ticket->subject)
            ->line('Cliente: ' . ($this->ticket->client->name ?? 'N/A'))
            ->action('Ver ticket', url('/admin/tickets/' . $this->ticket->id))
            ->line('¡Gracias por utilizar nuestra aplicación!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'client' => $this->ticket->client->name ?? 'N/A',
            'message' => 'Nuevo ticket creado: ' . $this->ticket->subject,
            'url' => '/admin/tickets/' . $this->ticket->id,
        ];
    }
}