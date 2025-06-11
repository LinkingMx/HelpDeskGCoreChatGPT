<?php

namespace App\Filament\Resources\AssetStatusResource\Pages;

use App\Filament\Resources\AssetStatusResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetStatus extends CreateRecord
{
    protected static string $resource = AssetStatusResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Estado de activo creado exitosamente';
    }
}
