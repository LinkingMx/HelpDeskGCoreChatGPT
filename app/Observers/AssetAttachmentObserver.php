<?php

namespace App\Observers;

use App\Models\AssetAttachment;
use Illuminate\Support\Facades\Storage;

class AssetAttachmentObserver
{
    /**
     * Handle the AssetAttachment "creating" event.
     */
    public function creating(AssetAttachment $assetAttachment): void
    {
        if ($assetAttachment->file_path) {
            // Obtener información del archivo
            $filePath = storage_path('app/public/'.$assetAttachment->file_path);

            if (file_exists($filePath)) {
                $assetAttachment->file_name = basename($assetAttachment->file_path);
                $assetAttachment->file_size = filesize($filePath);
                $assetAttachment->mime_type = mime_content_type($filePath);
            }
        }

        // Asignar usuario actual si no está especificado
        if (! $assetAttachment->uploaded_by) {
            $assetAttachment->uploaded_by = auth()->id();
        }
    }

    /**
     * Handle the AssetAttachment "updating" event.
     */
    public function updating(AssetAttachment $assetAttachment): void
    {
        if ($assetAttachment->isDirty('file_path') && $assetAttachment->file_path) {
            // Actualizar información del archivo si cambió
            $filePath = storage_path('app/public/'.$assetAttachment->file_path);

            if (file_exists($filePath)) {
                $assetAttachment->file_name = basename($assetAttachment->file_path);
                $assetAttachment->file_size = filesize($filePath);
                $assetAttachment->mime_type = mime_content_type($filePath);
            }
        }
    }

    /**
     * Handle the AssetAttachment "deleted" event.
     */
    public function deleted(AssetAttachment $assetAttachment): void
    {
        // Eliminar archivo físico cuando se elimina el registro
        if ($assetAttachment->file_path && Storage::disk('public')->exists($assetAttachment->file_path)) {
            Storage::disk('public')->delete($assetAttachment->file_path);
        }
    }
}
