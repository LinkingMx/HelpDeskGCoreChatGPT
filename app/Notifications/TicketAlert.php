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
            ->subject($this->title)
            ->line($this->body)
            ->action('Ver Ticket', url("/admin/tickets/{$this->ticket->id}"));
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
