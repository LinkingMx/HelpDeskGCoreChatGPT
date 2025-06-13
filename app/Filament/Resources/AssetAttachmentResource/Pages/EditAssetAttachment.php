<?php

namespace App\Filament\Resources\AssetAttachmentResource\Pages;

use App\Filament\Resources\AssetAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetAttachment extends EditRecord
{
    protected static string $resource = AssetAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
