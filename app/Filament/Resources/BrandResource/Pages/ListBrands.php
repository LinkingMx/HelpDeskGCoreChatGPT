<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBrands extends ListRecords
{
    protected static string $resource = BrandResource::class;

    protected static ?string $title = '🏷️ Marcas de Activos';

    public function getSubheading(): ?string
    {
        return '📋 Gestiona las marcas de equipos y activos para facilitar la organización del inventario.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Registrar Marca')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
