<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

    protected static ?string $title = '🏷️ Registrar Nueva Marca';

    public function getSubheading(): ?string
    {
        return '📝 Registra una nueva marca de equipos o activos para facilitar la gestión del inventario.';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->icon('heroicon-o-check-circle')
            ->title('✅ Marca Registrada')
            ->body("La marca '{$this->record->name}' ha sido registrada exitosamente. Ya puedes asignarla a tus activos.")
            ->duration(5000);
    }
}
