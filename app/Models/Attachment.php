<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_comment_id',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    /**
     * Get the ticket comment that this attachment belongs to.
     */
    public function ticketComment(): BelongsTo
    {
        return $this->belongsTo(TicketComment::class);
    }

    /**
     * Get the ticket comment that this attachment belongs to (alias).
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(TicketComment::class, 'ticket_comment_id');
    }
}
