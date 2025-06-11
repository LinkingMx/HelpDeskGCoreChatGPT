<?php

namespace App\Livewire;

use App\Models\Ticket;
use Livewire\Component;

class TicketTimeline extends Component
{
    public Ticket $ticket;

    public function getEventsProperty()
    {
        return \Spatie\Activitylog\Models\Activity::forSubject($this->ticket)
            ->where('log_name', 'ticket') // canal “ticket”
            ->with('causer')
            ->oldest()
            ->get();
    }

    public function render()
    {
        return view('livewire.ticket-timeline');
    }
}
