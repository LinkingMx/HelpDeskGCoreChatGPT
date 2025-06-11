<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\TicketComment;
use Livewire\Component;
use Livewire\WithFileUploads;
use Filament\Notifications\Notification;

class TicketConversation extends Component
{
    use WithFileUploads;

    public Ticket $ticket;
    public $comment = '';
    public $attachments = [];

    protected $rules = [
        'comment' => 'required|string|min:3',
        'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
    ];

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function addComment()
    {
        $this->validate();

        $comment = new TicketComment();
        $comment->ticket_id = $this->ticket->id;
        $comment->user_id = auth()->id();
        $comment->body = $this->comment;
        $comment->save();

        // Handle attachments if any
        foreach ($this->attachments as $attachment) {
            $path = $attachment->store('ticket-attachments', 'public');
            $comment->attachments()->create([
                'path' => $path,
                'name' => $attachment->getClientOriginalName(),
                'mime_type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
            ]);
        }

        $this->reset('comment', 'attachments');
        $this->ticket->refresh();
    }

    
    public function render()
    {
        return view('livewire.ticket-conversation', [
            'comments' => $this->ticket->comments()->with(['author', 'attachments'])->latest()->get()
        ]);
    }
}
