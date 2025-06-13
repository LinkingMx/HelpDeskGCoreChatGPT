<?php

namespace App\Filament\Resources\AssetAttachmentResource\Pages;

use App\Filament\Resources\AssetAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssetAttachments extends ListRecords
{
    protected static string $resource = AssetAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
