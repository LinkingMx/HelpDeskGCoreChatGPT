<?php

namespace App\Filament\Resources\AssetStatusResource\Pages;

use App\Filament\Resources\AssetStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetStatus extends EditRecord
{
    protected static string $resource = AssetStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Estado de activo actualizado exitosamente';
    }
}
