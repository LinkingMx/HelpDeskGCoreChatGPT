<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    /**
     * Get ticket details by ticket number including conversation
     */
    public function show(int $ticketNumber): JsonResponse
    {
        try {
            // Find ticket by ID (ticket number)
            $ticket = Ticket::with([
                'client',
                'department',
                'category',
                'status',
                'agent',
                'user',
                'comments.user',
            ])->find($ticketNumber);

            if (! $ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket no encontrado',
                    'data' => null,
                ], 404);
            }

            // Get attachments separately to avoid relationship issues
            $attachments = collect();
            try {
                $attachments = $ticket->attachments;
            } catch (\Exception $e) {
                // If attachments relationship fails, continue without them
            }

            // Format the ticket data
            $ticketData = [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'description' => $ticket->description,
                'priority' => $ticket->priority,
                'priority_text' => $this->getPriorityText($ticket->priority),
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $ticket->updated_at->format('Y-m-d H:i:s'),
                'created_at_human' => $ticket->created_at->diffForHumans(),
                'updated_at_human' => $ticket->updated_at->diffForHumans(),

                // Client information
                'client' => $ticket->client ? [
                    'id' => $ticket->client->id,
                    'name' => $ticket->client->name,
                    'contact_email' => $ticket->client->contact_email,
                    'contact_phone' => $ticket->client->contact_phone,
                ] : null,

                // Department information
                'department' => $ticket->department ? [
                    'id' => $ticket->department->id,
                    'name' => $ticket->department->name,
                    'description' => $ticket->department->description,
                ] : null,

                // Category information
                'category' => $ticket->category ? [
                    'id' => $ticket->category->id,
                    'name' => $ticket->category->name,
                    'icon' => $ticket->category->icon,
                    'color' => $ticket->category->color,
                    'time' => $ticket->category->time,
                ] : null,

                // Status information
                'status' => $ticket->status ? [
                    'id' => $ticket->status->id,
                    'name' => $ticket->status->name,
                    'color' => $ticket->status->color,
                    'description' => $ticket->status->description,
                ] : null,

                // Assigned agent information
                'agent' => $ticket->agent ? [
                    'id' => $ticket->agent->id,
                    'name' => $ticket->agent->name,
                    'email' => $ticket->agent->email,
                    'position' => $ticket->agent->position,
                ] : null,

                // Ticket creator information
                'creator' => $ticket->user ? [
                    'id' => $ticket->user->id,
                    'name' => $ticket->user->name,
                    'email' => $ticket->user->email,
                ] : null,

                // SLA information
                'sla' => $this->getSlaInfo($ticket),

                // Conversation (comments)
                'conversation' => $ticket->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'message' => $comment->body,
                        'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                        'created_at_human' => $comment->created_at->diffForHumans(),
                        'user' => $comment->user ? [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                            'email' => $comment->user->email,
                            'position' => $comment->user->position,
                        ] : null,
                    ];
                })->sortBy('created_at')->values(),

                // Attachments
                'attachments' => $attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_name' => $attachment->original_name,
                        'file_path' => $attachment->path,
                        'file_size' => $attachment->size,
                        'mime_type' => $attachment->mime,
                        'created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
                    ];
                }),

                // Statistics
                'statistics' => [
                    'total_comments' => $ticket->comments->count(),
                    'time_elapsed_hours' => $ticket->created_at->diffInHours(now()),
                    'time_elapsed_human' => $ticket->created_at->diffForHumans(),
                    'last_activity' => $ticket->comments->max('created_at')
                        ? $ticket->comments->max('created_at')->diffForHumans()
                        : $ticket->created_at->diffForHumans(),
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Ticket encontrado',
                'data' => $ticketData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error procesando la solicitud',
            ], 500);
        }
    }

    /**
     * Get priority text representation
     */
    private function getPriorityText(int $priority): string
    {
        return match ($priority) {
            1 => 'Alta',
            2 => 'Media',
            3 => 'Baja',
            default => 'Media',
        };
    }

    /**
     * Get SLA information for the ticket
     */
    private function getSlaInfo(Ticket $ticket): array
    {
        if (! $ticket->category || ! $ticket->category->time) {
            return [
                'defined' => false,
                'status' => 'Sin SLA definido',
                'elapsed_hours' => $ticket->created_at->diffInHours(now()),
                'remaining_hours' => null,
                'overdue_hours' => null,
                'percentage_used' => null,
            ];
        }

        $slaHours = (int) $ticket->category->time;
        $elapsedHours = $ticket->created_at->diffInHours(now());
        $remainingHours = max(0, $slaHours - $elapsedHours);
        $overdueHours = max(0, $elapsedHours - $slaHours);
        $percentageUsed = min(100, round(($elapsedHours / $slaHours) * 100, 2));

        $status = 'En tiempo';
        if ($elapsedHours >= $slaHours * 1.5) {
            $status = 'Excedido';
        } elseif ($elapsedHours >= $slaHours) {
            $status = 'Atención';
        }

        return [
            'defined' => true,
            'sla_hours' => $slaHours,
            'elapsed_hours' => $elapsedHours,
            'remaining_hours' => $remainingHours > 0 ? $remainingHours : null,
            'overdue_hours' => $overdueHours > 0 ? $overdueHours : null,
            'percentage_used' => $percentageUsed,
            'status' => $status,
        ];
    }
}
