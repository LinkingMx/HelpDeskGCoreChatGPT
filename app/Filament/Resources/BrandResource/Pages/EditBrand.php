<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

    protected static ?string $title = '✏️ Editar Marca';

    public function getSubheading(): ?string
    {
        return '🔧 Modifica la información de la marca registrada en el sistema.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar Marca')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('⚠️ Eliminar Marca')
                ->modalDescription('¿Estás seguro de que quieres eliminar esta marca? 

⚠️ **ADVERTENCIA:** Esta acción puede afectar:
• Los activos que tienen asignada esta marca
• Los reportes y estadísticas relacionados

Los activos asociados quedarán sin marca asignada.')
                ->modalSubmitActionLabel('Sí, eliminar marca')
                ->modalCancelActionLabel('Cancelar')
                ->successNotification(
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Marca eliminada')
                        ->body('La marca ha sido eliminada exitosamente.')
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->icon('heroicon-o-check-circle')
            ->title('✅ Marca Actualizada')
            ->body("Los cambios en la marca '{$this->record->name}' han sido guardados correctamente.")
            ->duration(5000);
    }
}
