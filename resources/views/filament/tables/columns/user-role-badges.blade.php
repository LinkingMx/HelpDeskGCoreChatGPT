@php
    $roles = $getRecord()->getRoleNames();
@endphp

<div class="flex flex-wrap gap-1">
    @foreach($roles as $role)
        @php
            $color = match($role) {
                'admin', 'super_admin' => 'primary',
                'agent' => 'success',
                default => 'gray'
            };
        @endphp
        
        <x-filament::badge :color="$color">
            {{ $role }}
        </x-filament::badge>
    @endforeach
</div>