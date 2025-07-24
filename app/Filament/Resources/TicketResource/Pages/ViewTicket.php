<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketStatus;
use App\Notifications\TicketAlert;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            // Acción para cerrar ticket
            Actions\Action::make('close_ticket')
                ->label('Cerrar Ticket')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cerrar Ticket')
                ->modalDescription('¿Estás seguro de que quieres cerrar este ticket? Solo tú podrás reabrirlo.')
                ->modalSubmitActionLabel('Sí, cerrar ticket')
                ->visible(function (): bool {
                    return auth()->id() === $this->record->user_id
                        && $this->record->status->name === 'Completado';
                })
                ->action(function () {
                    $closedStatus = TicketStatus::where('name', 'Cerrado')->first();
                    $this->record->update(['status_id' => $closedStatus->id]);

                    if ($this->record->agent) {
                        $this->record->agent->notify(new TicketAlert(
                            $this->record,
                            'Ticket #'.$this->record->id.' cerrado',
                            'El ticket "'.$this->record->subject.'" ha sido cerrado por el cliente.'
                        ));
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Ticket cerrado exitosamente')
                        ->success()
                        ->send();
                }),

            // Acción para reabrir ticket
            Actions\Action::make('reopen_ticket')
                ->label('Reabrir Ticket')
                ->icon('heroicon-o-lock-open')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Reabrir Ticket')
                ->modalDescription('¿Estás seguro de que quieres reabrir este ticket?')
                ->modalSubmitActionLabel('Sí, reabrir ticket')
                ->visible(function (): bool {
                    return auth()->id() === $this->record->user_id
                        && $this->record->status->name === 'Cerrado';
                })
                ->action(function () {
                    $completedStatus = TicketStatus::where('name', 'Completado')->first();
                    $this->record->update(['status_id' => $completedStatus->id]);

                    if ($this->record->agent) {
                        $this->record->agent->notify(new TicketAlert(
                            $this->record,
                            'Ticket #'.$this->record->id.' reabierto',
                            'El ticket "'.$this->record->subject.'" ha sido reabierto por el cliente.'
                        ));
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Ticket reabierto exitosamente')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header del ticket rediseñado - sin borde blanco y sin título
                Infolists\Components\Grid::make([
                    'default' => 3,    // Por defecto: 3 columnas
                    'md' => 4,         // Pantallas medianas: 4 columnas
                    'lg' => 4,         // Pantallas grandes: 4 columnas
                ])
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label(false)
                            ->formatStateUsing(fn ($state) => "Ticket #$state")
                            ->badge()
                            ->color('gray')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->icon('heroicon-o-ticket'),

                        Infolists\Components\TextEntry::make('status.name')
                            ->label(false)
                            ->badge()
                            ->color(fn ($record) => $record->status->color ?? 'gray')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                        Infolists\Components\TextEntry::make('priority')
                            ->label(false)
                            ->badge()
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->color(fn (int $state): string => match ($state) {
                                1 => 'danger',
                                2 => 'warning',
                                3 => 'success',
                                default => 'warning',
                            })
                            ->formatStateUsing(fn (int $state): string => match ($state) {
                                1 => '🔥 Alta',
                                2 => '⚠️ Media',
                                3 => '✅ Baja',
                                default => 'Media',
                            }),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label(false)
                            ->formatStateUsing(fn ($state) => $state->diffForHumans())
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columnSpanFull(),

                // Layout principal con conversación prominente
                Infolists\Components\Grid::make([
                    'sm' => 1,
                    'xl' => 5,
                ])
                    ->schema([
                        // Conversación - Toma más espacio (3/5)
                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Conversación del Ticket')
                                ->description('Intercambio de mensajes en tiempo real')
                                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                                ->iconColor('primary')
                                ->extraAttributes([
                                    'class' => 'h-conversation-container',
                                ])
                                ->schema([
                                    Livewire::make(\App\Livewire\EnhancedTicketConversation::class, [
                                        'ticket' => $this->record,
                                    ]),
                                ]),
                        ])->columnSpan([
                            'sm' => 1,
                            'xl' => 3,
                        ]),

                        // Información del ticket - Panel lateral (2/5)
                        Infolists\Components\Group::make([
                            // Resumen rápido
                            Infolists\Components\Section::make('Resumen')
                                ->icon('heroicon-o-clipboard-document-list')
                                ->collapsible()
                                ->schema([
                                    Infolists\Components\Grid::make(1)
                                        ->schema([
                                            Infolists\Components\TextEntry::make('department.name')
                                                ->label('Departamento')
                                                ->badge()
                                                ->color('primary')
                                                ->icon('heroicon-o-building-office'),

                                            Infolists\Components\TextEntry::make('category.name')
                                                ->label('Categoría')
                                                ->badge()
                                                ->color(fn ($record) => $record->category->color ?? 'secondary')
                                                ->icon('heroicon-o-tag'),

                                            Infolists\Components\TextEntry::make('agent.name')
                                                ->label('Agente asignado')
                                                ->placeholder('Sin asignar')
                                                ->weight(FontWeight::Bold)
                                                ->color('success'),
                                        ]),
                                ]),

                            // Archivos adjuntos del ticket
                            Infolists\Components\Section::make('Archivos Adjuntos')
                                ->icon('heroicon-o-paper-clip')
                                ->collapsible()
                                ->schema([
                                    Infolists\Components\RepeatableEntry::make('attachments')
                                        ->label('')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('original_name')
                                                ->label('Archivo')
                                                ->formatStateUsing(function ($record) {
                                                    $size = number_format($record->size / 1024, 1);
                                                    return $record->original_name . ' (' . $size . 'KB)';
                                                })
                                                ->icon('heroicon-o-document')
                                                ->suffixActions([
                                                    Infolists\Components\Actions\Action::make('view')
                                                        ->icon('heroicon-o-eye')
                                                        ->color('primary')
                                                        ->tooltip('Ver archivo')
                                                        ->url(fn ($record) => route('attachments.view', $record))
                                                        ->openUrlInNewTab(),
                                                    Infolists\Components\Actions\Action::make('download')
                                                        ->icon('heroicon-o-arrow-down-tray')
                                                        ->color('gray')
                                                        ->tooltip('Descargar archivo')
                                                        ->url(fn ($record) => route('attachments.download', $record))
                                                ]),
                                        ])
                                        ->contained(false),
                                ]),

                            // Descripción del problema
                            Infolists\Components\Section::make('Descripción del Problema')
                                ->icon('heroicon-o-document-text')
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    Infolists\Components\TextEntry::make('description')
                                        ->label(false)
                                        ->html()
                                        ->prose()
                                        ->extraAttributes([
                                            'class' => 'p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border-l-4 border-blue-400 shadow-sm text-sm max-h-48 overflow-y-auto',
                                        ]),
                                ]),

                            // Participantes
                            Infolists\Components\Section::make('Participantes')
                                ->icon('heroicon-o-users')
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('client.name')
                                            ->label('Cliente')
                                            ->weight(FontWeight::Bold)
                                            ->icon('heroicon-o-user')
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('client.contact_email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable()
                                            ->copyMessage('Email copiado'),

                                        Infolists\Components\TextEntry::make('client.contact_phone')
                                            ->label('Teléfono')
                                            ->icon('heroicon-o-phone')
                                            ->copyable()
                                            ->copyMessage('Teléfono copiado'),
                                    ]),
                                ]),

                            // Métricas y SLA
                            Infolists\Components\Section::make('Métricas SLA')
                                ->icon('heroicon-o-chart-bar')
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    Infolists\Components\Grid::make(1)
                                        ->schema([
                                            Infolists\Components\TextEntry::make('sla_status')
                                                ->label('Estado SLA')
                                                ->formatStateUsing(function ($record) {
                                                    if (! $record->category || ! $record->category->time) {
                                                        return '⚡ Sin SLA definido';
                                                    }

                                                    $sla = (int) $record->category->time; // Horas de SLA
                                                    $elapsed = $record->created_at->diffInHours(now()); // Horas transcurridas

                                                    if ($elapsed < $sla) {
                                                        return "✅ En tiempo ({$elapsed}h / {$sla}h)";
                                                    } elseif ($elapsed < $sla * 1.5) {
                                                        return "⚠️ Atención ({$elapsed}h / {$sla}h)";
                                                    } else {
                                                        return "❌ Excedido ({$elapsed}h / {$sla}h)";
                                                    }
                                                })
                                                ->badge()
                                                ->color(function ($record) {
                                                    if (! $record->category || ! $record->category->time) {
                                                        return 'gray';
                                                    }

                                                    $sla = (int) $record->category->time;
                                                    $elapsed = $record->created_at->diffInHours(now());

                                                    if ($elapsed < $sla) {
                                                        return 'success';
                                                    } elseif ($elapsed < $sla * 1.5) {
                                                        return 'warning';
                                                    } else {
                                                        return 'danger';
                                                    }
                                                }),

                                            Infolists\Components\TextEntry::make('sla_remaining')
                                                ->label('Tiempo restante SLA')
                                                ->formatStateUsing(function ($record) {
                                                    if (! $record->category || ! $record->category->time) {
                                                        return 'N/A';
                                                    }

                                                    $sla = (int) $record->category->time;
                                                    $elapsed = $record->created_at->diffInHours(now());
                                                    $remaining = max(0, $sla - $elapsed);

                                                    if ($remaining > 0) {
                                                        return "{$remaining} horas restantes";
                                                    } else {
                                                        $overdue = $elapsed - $sla;

                                                        return "Excedido por {$overdue} horas";
                                                    }
                                                })
                                                ->icon('heroicon-o-clock')
                                                ->color('info'),

                                            Infolists\Components\TextEntry::make('time_elapsed')
                                                ->label('Tiempo activo')
                                                ->formatStateUsing(fn ($record) => $record->created_at->diffForHumans())
                                                ->icon('heroicon-o-calendar')
                                                ->color('gray'),

                                            Infolists\Components\TextEntry::make('comments_count')
                                                ->label('Interacciones')
                                                ->formatStateUsing(fn ($record) => $record->comments()->count().' mensajes')
                                                ->badge()
                                                ->color('info')
                                                ->icon('heroicon-o-chat-bubble-left-right'),
                                        ]),
                                ]),

                            // Actividad reciente
                            Infolists\Components\Section::make('Actividad Reciente')
                                ->icon('heroicon-o-clock')
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    Livewire::make(\App\Livewire\TicketTimeline::class, [
                                        'ticket' => $this->record,
                                    ]),
                                ]),

                        ])->columnSpan([
                            'sm' => 1,
                            'xl' => 2,
                        ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->extraAttributes([
                'style' => '
                    .h-conversation-container { 
                        height: calc(100vh - 12rem); 
                        min-height: 600px; 
                    }
                    @media (max-width: 1279px) {
                        .h-conversation-container { 
                            height: auto; 
                            min-height: 500px; 
                        }
                    }
                ',
            ]);
    }
}
