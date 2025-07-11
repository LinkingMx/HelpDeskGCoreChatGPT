<?php

namespace App\Filament\Resources\TicketCategoryResource\Pages;

use App\Filament\Resources\TicketCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketCategories extends ListRecords
{
    protected static string $resource = TicketCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Categoría')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    public function getTitle(): string
    {
        return 'Categorías de Tickets';
    }

    public function getSubheading(): ?string
    {
        return 'Gestiona las categorías disponibles para clasificar los tickets del sistema.';
    }
}
