<?php

namespace App\Filament\Resources\AssetStatusResource\Pages;

use App\Filament\Resources\AssetStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssetStatuses extends ListRecords
{
    protected static string $resource = AssetStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
