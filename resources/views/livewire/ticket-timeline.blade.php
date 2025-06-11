<div class="flex flex-col gap-4">
    @foreach($this->events as $e)
      <div class="relative pl-8">
          <x-filament::icon :name="match($e->event) {
              'created'          => 'heroicon-o-plus-circle',
              'status_id_changed'=> 'heroicon-academic-cap',
              'priority_changed' => 'heroicon-o-bolt',
              'agent_id_changed' => 'heroicon-o-user-circle',
              'commented'        => 'heroicon-o-chat-bubble-left-right',
              default            => 'heroicon-o-information-circle',
          }" class="absolute -left-1.5 top-1.5 text-primary-500"/>
    
          <p class="font-medium">
             {{ ucfirst(str_replace('_', ' ', $e->event)) }}
             @if($e->causer) by {{ $e->causer->name }} change {{ $e->causer->type }} @endif
          </p>
          <p class="text-xs opacity-70">{{ $e->created_at->diffForHumans() }}</p>
      </div>
    @endforeach
    </div>