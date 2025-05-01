<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Everyone can see the list of tickets based on their other permissions
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // super_admin & admin: true
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // agent: allowed if ticket->agent_id == user->id OR ticket->department_id == user->department_id
        if ($user->hasRole('agent')) {
            return $ticket->agent_id === $user->id || $ticket->department_id === $user->department_id;
        }

        // user: allowed if ticket->user_id == user->id
        return $ticket->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create tickets
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // super_admin & admin: true
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // agent: allowed if ticket->agent_id == user->id OR ticket->department_id == user->department_id
        if ($user->hasRole('agent')) {
            return $ticket->agent_id === $user->id || $ticket->department_id === $user->department_id;
        }

        // user: allowed if ticket->user_id == user->id
        return $ticket->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Only admins and super_admins can delete tickets
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        // Only admins and super_admins can restore tickets
        return $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        // Only admins and super_admins can force delete tickets
        return $user->hasRole(['super_admin', 'admin']);
    }
}