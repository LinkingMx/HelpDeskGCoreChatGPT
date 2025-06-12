<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAlert;
use Illuminate\Support\Collection;

/**
 * TicketObserver
 *
 * Observa eventos de Ticket y envía alertas a los usuarios correspondientes.
 */
class TicketObserver
{
    private function dispatchAlerts(Collection $users, Ticket $ticket, string $title, string $body): void
    {
        $alert = new TicketAlert($ticket, $title, $body);
        // envía (usa cola porque ShouldQueue)
        $users->each->notify($alert);

        // Guarda en activity_log quiénes fueron notificados:
        activity('ticket')
            ->performedOn($ticket)
            ->causedBy(auth()->user() ?? User::find(1))
            ->withProperties([
                'notified' => $users->pluck('name')->all(),
                'title' => $title,
            ])
            ->event('alert_sent')
            ->log($body);
    }

    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        // Obtener agentes del departamento
        $agents = User::role('agent')
            ->where('department_id', $ticket->department_id)
            ->get();

        // Obtener el creador del ticket
        $creator = User::find($ticket->user_id);

        // Notificar a los agentes del departamento (excepto al creador si es agente)
        $agentsToNotify = $agents->reject(function ($agent) use ($creator) {
            return $creator && $agent->id === $creator->id;
        });

        if ($agentsToNotify->isNotEmpty()) {
            $this->dispatchAlerts(
                $agentsToNotify,
                $ticket,
                "Nuevo ticket #{$ticket->id}",
                "Se creó el ticket «{$ticket->subject}» en el departamento {$ticket->department->name}. Revísalo y asígnalo."
            );
        }

        // Siempre notificar al creador del ticket (confirmación)
        if ($creator) {
            $message = $creator->hasRole('agent') && $creator->department_id == $ticket->department_id
                ? "Has creado el ticket «{$ticket->subject}» en tu departamento {$ticket->department->name}. Puedes asignártelo o dejarlo para otros agentes."
                : "Tu ticket «{$ticket->subject}» ha sido creado y asignado al departamento {$ticket->department->name}. En breve un agente lo revisará.";

            $this->dispatchAlerts(
                collect([$creator]),
                $ticket,
                "Ticket #{$ticket->id} creado exitosamente",
                $message
            );
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        $dirty = $ticket->getDirty();
        if (! $dirty) {
            return;
        }

        // Descripción de cambios
        $changes = collect($dirty)->keys()
            ->map(fn ($f) => str_replace('_id', '', $f))
            ->implode(', ');

        if (is_null($ticket->agent_id)) {
            /* Escenario 2: sin agente */
            $agents = User::role('agent')
                ->where('department_id', $ticket->department_id)
                ->get();

            $this->dispatchAlerts(
                $agents,
                $ticket,
                "Ticket #{$ticket->id} actualizado (sin agente)",
                "El ticket «{$ticket->subject}» cambió ({$changes}) y sigue sin agente asignado."
            );
        } else {
            /* Escenario 3: ya tiene agente */
            $recipients = collect([
                $ticket->agent,
                $ticket->opener,   // usuario creador
            ])->filter();

            $this->dispatchAlerts(
                $recipients,
                $ticket,
                "Ticket #{$ticket->id} actualizado",
                "El ticket «{$ticket->subject}» fue actualizado ({$changes})."
            );
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
