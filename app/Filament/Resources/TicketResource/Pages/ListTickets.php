<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketStatus;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    public function mount(): void
    {
        parent::mount();

        // Aplicar filtro por defecto si no hay filtros definidos
        if (! isset($this->tableFilters['include_closed'])) {
            $this->tableFilters['include_closed'] = false;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        // Agregar pestaña "Todos" (solo tickets activos por defecto)
        $tabs['all'] = Tab::make('Todos')
            ->badge(function () {
                // Contar todos los tickets activos según permisos del usuario
                $query = TicketResource::getEloquentQuery()
                    ->whereHas('status', fn (Builder $q) => $q->where('is_final', false));

                return $query->count();
            })
            ->badgeColor('primary') // Añadiendo color al badge de "Todos"
            ->modifyQueryUsing(function (Builder $query) {
                // Filtrar solo tickets activos (no cerrados)
                return $query->whereHas('status', fn (Builder $q) => $q->where('is_final', false));
            });

        // Obtener solo los estados activos (no finales) de tickets para las tabs
        $statuses = TicketStatus::where('is_final', false)->get();

        // Crear una pestaña para cada estado activo
        foreach ($statuses as $status) {
            // Usar el color del estado directamente del modelo
            $tabColor = $status->color ?? 'gray';

            $tabName = strtolower(str_replace(' ', '_', $status->name));

            $tabs[$tabName] = Tab::make($status->name)
                ->badge(function () use ($status) {
                    // Contar tickets filtrados por este estado
                    $query = TicketResource::getEloquentQuery()->where('status_id', $status->id);

                    return $query->count();
                })
                ->badgeColor($tabColor) // Usar el color del estado
                ->modifyQueryUsing(function (Builder $query) use ($status) {
                    // Filtrar la consulta por este estado
                    return $query->where('status_id', $status->id);
                });
        }

        return $tabs;
    }
}
