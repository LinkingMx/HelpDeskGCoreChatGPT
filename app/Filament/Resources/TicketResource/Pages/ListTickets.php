<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketStatus;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];
        
        // Agregar pestaña "Todos"
        $tabs['all'] = Tab::make('Todos')
            ->badge(function () {
                // Contar todos los tickets según permisos del usuario
                $query = TicketResource::getEloquentQuery();
                return $query->count();
            })
            ->badgeColor('primary'); // Añadiendo color al badge de "Todos"
            
        // Obtener todos los estados de tickets
        $statuses = TicketStatus::all();
        
        // Crear una pestaña para cada estado
        foreach ($statuses as $status) {
            // Usar directamente el color del modelo en lugar del match predefinido
            $tabColor = $status->color ?? 'gray';
            
            $tabName = strtolower(str_replace(' ', '_', $status->name));
            
            $tabs[$tabName] = Tab::make($status->name)
                ->badge(function () use ($status) {
                    // Contar tickets filtrados por este estado
                    $query = TicketResource::getEloquentQuery()->where('status_id', $status->id);
                    return $query->count();
                })
                ->badgeColor($tabColor)
                ->modifyQueryUsing(function (Builder $query) use ($status) {
                    // Filtrar la consulta por este estado
                    return $query->where('status_id', $status->id);
                });
        }
        
        return $tabs;
    }
}
