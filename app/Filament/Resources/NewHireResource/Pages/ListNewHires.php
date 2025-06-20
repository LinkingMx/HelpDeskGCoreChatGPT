<?php

namespace App\Filament\Resources\NewHireResource\Pages;

use App\Filament\Resources\NewHireResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewHires extends ListRecords
{
    protected static string $resource = NewHireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo Ingreso'),
        ];
    }
}
