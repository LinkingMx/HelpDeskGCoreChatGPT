<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'user_id',
        'agent_id',
        'department_id',
        'status_id',
        'category_id',
        'subject',
        'description',
        'priority',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($ticket) {
            // Asignar estado "Iniciado" por defecto si no se especifica
            if (! $ticket->status_id) {
                $ticket->status_id = 1; // ID del estado "Iniciado"
            }

            // Set department_id based on agent's department when it's missing
            if (empty($ticket->department_id) && ! empty($ticket->agent_id)) {
                $agent = User::find($ticket->agent_id);
                if ($agent && $agent->department_id) {
                    $ticket->department_id = $agent->department_id;
                }
            }
        });
    }

    /**
     * Get the client that this ticket belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who opened this ticket.
     */
    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the agent assigned to this ticket.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the department this ticket belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the status of this ticket.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class);
    }

    /**
     * Get the category of this ticket.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class);
    }

    /**
     * Get the comments for this ticket.
     */
    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }
}
