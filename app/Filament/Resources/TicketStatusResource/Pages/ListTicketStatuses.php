<?php

namespace App\Filament\Resources\TicketStatusResource\Pages;

use App\Filament\Resources\TicketStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketStatuses extends ListRecords
{
    protected static string $resource = TicketStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo Estado')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    public function getTitle(): string
    {
        return 'Estados de Tickets';
    }

    public function getSubheading(): ?string
    {
        return 'Gestiona los diferentes estados que pueden tener los tickets en el sistema.';
    }
}
