<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketDepartmentUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Ticket $ticket, public string $message)
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
            ->subject('Actualización en ticket #' . $this->ticket->id)
            ->greeting('Hola ' . $notifiable->name)
            ->line($this->message)
            ->line('Ticket #' . $this->ticket->id . ': ' . $this->ticket->subject)
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
            'message' => $this->message,
            'url' => '/admin/tickets/' . $this->ticket->id,
        ];
    }
}
