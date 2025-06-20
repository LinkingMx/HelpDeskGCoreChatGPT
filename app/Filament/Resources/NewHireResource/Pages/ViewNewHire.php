<?php

namespace App\Filament\Resources\NewHireResource\Pages;

use App\Filament\Resources\NewHireResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNewHire extends ViewRecord
{
    protected static string $resource = NewHireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar')
                ->icon('heroicon-o-pencil-square'),
            Actions\Action::make('back_to_list')
                ->label('Volver al Listado')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => $this->getResource()::getUrl('index')),
        ];
    }
}
