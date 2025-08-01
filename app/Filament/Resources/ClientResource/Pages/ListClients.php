<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Clientes Registrados';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Registrar Cliente')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
