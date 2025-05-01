<?php

namespace App\Notifications;

use App\Models\TicketComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Illuminate\Support\Str;

class TicketCommentAdded extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The ticket comment instance.
     *
     * @var \App\Models\TicketComment
     */
    protected $ticketComment;

    /**
     * Create a new notification instance.
     */
    public function __construct(TicketComment $ticketComment)
    {
        $this->ticketComment = $ticketComment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticketComment->ticket;
        $commentAuthor = $this->ticketComment->author;
        $previewBody = Str::limit($this->ticketComment->body, 100);
        
        return (new MailMessage)
            ->subject("Reply on Ticket #{$ticket->id} - {$ticket->subject}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("{$commentAuthor->name} has replied to ticket #{$ticket->id}:")
            ->line($previewBody)
            ->action('View Ticket', url("/admin/tickets/{$ticket->id}"))
            ->line('Thank you for using our ticketing system!');
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable)
    {
        $ticket = $this->ticketComment->ticket;
        $bodySnippet = Str::limit($this->ticketComment->body, 80);
        
        return (new WebPushMessage)
            ->title("New reply on #{$ticket->id} - {$ticket->subject}")
            ->body($bodySnippet)
            ->icon('/icons/icon-192x192.png')
            ->data(['url' => "/admin/tickets/{$ticket->id}"]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $ticket = $this->ticketComment->ticket;
        
        return [
            'ticket_id' => $ticket->id,
            'ticket_subject' => $ticket->subject,
            'comment_id' => $this->ticketComment->id,
            'comment_body' => Str::limit($this->ticketComment->body, 150),
            'author_id' => $this->ticketComment->user_id,
            'author_name' => $this->ticketComment->author->name,
            'created_at' => $this->ticketComment->created_at,
        ];
    }
}
