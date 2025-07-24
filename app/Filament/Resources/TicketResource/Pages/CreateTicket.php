<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketComment;
use App\Models\Attachment;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    // Propiedad para almacenar adjuntos pendientes
    protected $pendingAttachments = [];

    // Add redirect URL method
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
        // Debug: Log para ver qué hay en los datos
        \Log::info('Datos del formulario antes de crear ticket:', $data);
        
        // Obtener archivos directamente del estado del formulario
        $this->pendingAttachments = $data['attachments'] ?? [];
        
        // Debug: Log para ver los adjuntos pendientes
        \Log::info('Adjuntos pendientes:', [
            'count' => count($this->pendingAttachments),
            'attachments' => $this->pendingAttachments
        ]);
        
        // Remover attachments de los datos del ticket para evitar errores
        unset($data['attachments']);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        \Log::info('AfterCreate ejecutándose para ticket #' . $this->record->id);
        
        // Verificar si hay archivos adjuntos pendientes
        if (!empty($this->pendingAttachments)) {
            \Log::info('Creando comentario inicial...');
            
            // Crear comentario inicial con los adjuntos
            $comment = TicketComment::create([
                'ticket_id' => $this->record->id,
                'user_id' => auth()->id(),
                'body' => 'Ticket creado con archivos adjuntos.',
                'is_internal' => false,
            ]);
            
            \Log::info('Comentario creado con ID: ' . $comment->id);

            // Procesar cada archivo adjunto
            foreach ($this->pendingAttachments as $index => $filePath) {
                \Log::info('Procesando adjunto #' . $index . ': ' . $filePath);
                
                try {
                    if (is_string($filePath)) {
                        // El archivo ya está guardado, obtener información del archivo
                        $fullPath = storage_path('app/public/' . $filePath);
                        
                        if (file_exists($fullPath)) {
                            // Extraer nombre original del path (Filament usa ULID + extension)
                            $pathInfo = pathinfo($filePath);
                            $extension = $pathInfo['extension'] ?? '';
                            
                            // Para el nombre original, usar un nombre basado en el timestamp
                            $originalName = 'archivo_adjunto_' . time() . '.' . $extension;
                            
                            $size = filesize($fullPath);
                            $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
                            
                            \Log::info('Información del archivo:', [
                                'original_name' => $originalName,
                                'mime_type' => $mimeType,
                                'size' => $size,
                                'path' => $filePath
                            ]);

                            // Crear registro de adjunto
                            $attachment = Attachment::create([
                                'ticket_comment_id' => $comment->id,
                                'path' => $filePath,
                                'original_name' => $originalName,
                                'mime' => $mimeType,
                                'size' => $size,
                            ]);
                            
                            \Log::info('Adjunto creado con ID: ' . $attachment->id);
                        } else {
                            \Log::error('Archivo no encontrado: ' . $fullPath);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al procesar adjunto en ticket: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                }
            }
        } else {
            \Log::info('No hay adjuntos pendientes');
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        $attachmentCount = count($this->pendingAttachments ?? []);
        $message = "El ticket '{$this->record->subject}' ha sido creado exitosamente.";
        
        if ($attachmentCount > 0) {
            $message .= " Se adjuntaron {$attachmentCount} archivo(s).";
        }

        return Notification::make()
            ->success()
            ->icon('heroicon-o-check-circle')
            ->title('Ticket Creado')
            ->body($message);
    }
}
