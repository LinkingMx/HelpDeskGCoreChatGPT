<?php
namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketDepartmentUpdated;
use App\Notifications\NewTicketCreated;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class TicketObserver
{
    /* ========== CREATED ========== */
    public function created(Ticket $ticket): void
    {
        /* --- 1. Notificamos a los agentes del departamento --- */
        $notified = $this->notifyDepartmentAgents($ticket, 'Nuevo ticket creado');

        /* --- 2. Registramos en activity_log --- */
        activity('ticket')
            ->performedOn($ticket)
            ->causedBy(Auth::user() ?? User::find(1))
            ->withProperties([
                'ticket_id' => $ticket->id,
                'subject' => $ticket->subject,
                'department_id' => $ticket->department_id,
                'notified_agents' => $notified,
            ])
            ->event('ticket_created')
            ->log('ticket_created');
    }

    /* ========== UPDATED ========== */
    public function updated(Ticket $ticket): void
    {
        $dirty = $ticket->getDirty();

        foreach (['status_id','priority','agent_id'] as $field) {
            if (! array_key_exists($field, $dirty)) {
                continue;
            }

            /* --- 1. Notificamos --- */
            $msg = match ($field) {
                'status_id' => 'Estado actualizado',
                'priority'  => 'Prioridad actualizada',
                'agent_id'  => 'Agente reasignado',
            };
            $notified = $this->notifyDepartmentAgents($ticket, $msg);

            /* --- 2. Registramos en activity_log --- */
            activity('ticket')
                ->performedOn($ticket)
                ->causedBy(Auth::user() ?? User::find(1))
                ->withProperties([
                    'field'           => $field,
                    'old'             => $ticket->getOriginal($field),
                    'new'             => $ticket->$field,
                    'notified_agents' => $notified,
                ])
                ->event("{$field}_changed")
                ->log("{$field}_changed");
        }
    }

    /**
     * Notifica a los agentes del departamento sobre cambios en el ticket.
     *
     * @param \App\Models\Ticket $ticket
     * @param string $message
     * @return array ID de los agentes notificados
     */
    protected function notifyDepartmentAgents(Ticket $ticket, string $message): array
    {
        // Si no hay departamento asignado, no podemos notificar
        if (!$ticket->department_id) {
            return [];
        }

        // Obtenemos los agentes del departamento
        $agents = User::role('agent')
            ->where('department_id', $ticket->department_id)
            ->get();

        // Si no hay agentes, no podemos notificar
        if ($agents->isEmpty()) {
            return [];
        }

        $notifiedIds = [];

        // Enviamos la notificación según el evento
        if ($message === 'Nuevo ticket creado') {
            foreach ($agents as $agent) {
                $agent->notify(new NewTicketCreated($ticket));
                $notifiedIds[] = $agent->id;
            }
        } else {
            foreach ($agents as $agent) {
                // Esta notificación se usará para las actualizaciones
                $agent->notify(new TicketDepartmentUpdated($ticket, $message));
                $notifiedIds[] = $agent->id;
            }
        }

        return $notifiedIds;
    }
}