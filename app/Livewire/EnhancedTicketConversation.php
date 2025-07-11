<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\TicketComment;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\WithFileUploads;

class EnhancedTicketConversation extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public $comment = '';

    public $attachments = [];

    public $isTyping = false;

    public $showAttachmentPreview = false;

    // Para auto-refresh
    public $lastCommentId = null;

    protected $rules = [
        'comment' => 'required|string|min:3|max:2000',
        'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip',
    ];

    protected $validationAttributes = [
        'comment' => 'comentario',
        'attachments.*' => 'archivo adjunto',
    ];

    protected $listeners = ['refreshConversation' => '$refresh'];

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->lastCommentId = $ticket->comments()->latest()->first()?->id;
    }

    public function updatedComment()
    {
        $this->isTyping = ! empty(trim($this->comment));
    }

    public function addComment()
    {
        $this->validate();

        try {
            $comment = new TicketComment;
            $comment->ticket_id = $this->ticket->id;
            $comment->user_id = auth()->id();
            $comment->body = $this->comment;
            $comment->save();

            // Handle attachments if any
            foreach ($this->attachments as $attachment) {
                if ($attachment) {
                    $path = $attachment->store('ticket-attachments', 'public');
                    $comment->attachments()->create([
                        'path' => $path,
                        'name' => $attachment->getClientOriginalName(),
                        'mime_type' => $attachment->getMimeType(),
                        'size' => $attachment->getSize(),
                    ]);
                }
            }

            // Reset form
            $this->reset('comment', 'attachments', 'showAttachmentPreview');
            $this->isTyping = false;

            // Refresh ticket and update last comment ID
            $this->ticket->refresh();
            $this->lastCommentId = $comment->id;

            // Emit event for scroll
            $this->dispatch('comment-added');

            // Show notification
            Notification::make()
                ->title('💬 Mensaje enviado')
                ->body('Tu comentario ha sido publicado exitosamente.')
                ->success()
                ->duration(3000)
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Error al enviar')
                ->body('No se pudo publicar el comentario. Inténtalo de nuevo.')
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function addAttachment()
    {
        $this->showAttachmentPreview = true;
    }

    public function removeAttachment($index)
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);

        if (empty($this->attachments)) {
            $this->showAttachmentPreview = false;
        }
    }

    public function getCommentCharacterCount()
    {
        return strlen($this->comment);
    }

    public function canSubmit()
    {
        return ! empty(trim($this->comment)) && strlen(trim($this->comment)) >= 3;
    }

    public function render()
    {
        $comments = $this->ticket->comments()
            ->with(['author.roles', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.enhanced-ticket-conversation', [
            'comments' => $comments,
            'totalComments' => $comments->count(),
        ]);
    }
}
