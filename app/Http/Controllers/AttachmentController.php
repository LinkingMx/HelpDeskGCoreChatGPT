<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AttachmentController extends Controller
{
    /**
     * Ver un adjunto en el navegador
     */
    public function view(Attachment $attachment)
    {
        $user = Auth::user();
        $ticket = $attachment->comment->ticket;

        // Verificar permisos de acceso al ticket
        if (!$this->canAccessTicket($user, $ticket)) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes permisos para acceder a este adjunto.');
        }

        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($attachment->path)) {
            abort(Response::HTTP_NOT_FOUND, 'El archivo no fue encontrado.');
        }

        $filePath = Storage::disk('public')->path($attachment->path);
        $mimeType = $attachment->mime;

        // Determinar si el archivo se puede mostrar en el navegador
        $viewableTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf',
            'text/plain', 'text/html', 'text/css', 'text/javascript',
            'application/json', 'application/xml'
        ];

        if (in_array($mimeType, $viewableTypes)) {
            // Mostrar el archivo directamente en el navegador
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"'
            ]);
        } else {
            // Para archivos no visualizables, mostrar una página de información
            return view('attachments.preview', compact('attachment', 'ticket'));
        }
    }

    /**
     * Descargar un adjunto de manera segura
     */
    public function download(Attachment $attachment)
    {
        $user = Auth::user();
        $ticket = $attachment->comment->ticket;

        // Verificar permisos de acceso al ticket
        if (!$this->canAccessTicket($user, $ticket)) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes permisos para acceder a este adjunto.');
        }

        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($attachment->path)) {
            abort(Response::HTTP_NOT_FOUND, 'El archivo no fue encontrado.');
        }

        // Descargar el archivo con el nombre original
        return Storage::disk('public')->download(
            $attachment->path,
            $attachment->original_name
        );
    }

    /**
     * Verificar si el usuario puede acceder al ticket
     */
    private function canAccessTicket($user, $ticket): bool
    {
        // Super admin puede ver todos los tickets
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Agent puede ver tickets de su departamento
        if ($user->hasRole('agent')) {
            return $user->department_id === $ticket->department_id;
        }

        // User solo puede ver sus propios tickets
        if ($user->hasRole('user')) {
            return $user->id === $ticket->user_id;
        }

        return false;
    }
}
