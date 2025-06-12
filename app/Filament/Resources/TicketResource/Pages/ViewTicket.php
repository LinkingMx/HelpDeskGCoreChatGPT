<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketStatus;
use App\Notifications\TicketAlert;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;

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
                // Layout principal con dos columnas
                Infolists\Components\Grid::make(3)
                    ->schema([
                        // Columna izquierda - Toda la información del ticket (2/3 del ancho)
                        Infolists\Components\Group::make([
                            // Encabezado del ticket
                            Infolists\Components\Section::make()
                                ->schema([
                                    Infolists\Components\TextEntry::make('id')
                                        ->label('Ticket #')
                                        ->badge()
                                        ->color('gray')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Medium),

                                    Infolists\Components\TextEntry::make('subject')
                                        ->label('Asunto')
                                        ->weight(FontWeight::Bold)
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                ])
                                ->columns(2),

                            // Descripción
                            Infolists\Components\Section::make('Descripción')
                                ->icon('heroicon-o-document-text')
                                ->collapsible()
                                ->schema([
                                    Infolists\Components\TextEntry::make('description')
                                        ->html()
                                        ->prose()
                                        ->extraAttributes([
                                            'class' => 'p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700',
                                        ]),
                                ]),

                            // Información principal del ticket
                            Infolists\Components\Section::make('Detalles')
                                ->description('Información detallada del ticket')
                                ->schema([
                                    Infolists\Components\TextEntry::make('status.name')
                                        ->label('Estado')
                                        ->badge()
                                        ->color(fn ($record) => $record->status->badge_color ?? 'gray')
                                        ->icon('heroicon-o-check-circle'),

                                    Infolists\Components\TextEntry::make('priority')
                                        ->label('Prioridad')
                                        ->badge()
                                        ->color(fn (int $state): string => match ($state) {
                                            1 => 'danger',
                                            2 => 'warning',
                                            3 => 'success',
                                            default => 'warning',
                                        })
                                        ->formatStateUsing(fn (int $state): string => match ($state) {
                                            1 => 'Alta',
                                            2 => 'Media',
                                            3 => 'Baja',
                                            default => 'Media',
                                        })
                                        ->icon('heroicon-o-flag'),

                                    Infolists\Components\TextEntry::make('department.name')
                                        ->label('Departamento')
                                        ->badge()
                                        ->color('primary')
                                        ->icon('heroicon-o-building-office'),

                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('Fecha de creación')
                                        ->date('d M Y, H:i')
                                        ->icon('heroicon-o-calendar'),
                                ])
                                ->columns(2),

                            // Sección de cliente y asignación
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Section::make('Cliente')
                                        ->icon('heroicon-o-users')
                                        ->iconColor('primary')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('client.name')
                                                ->label('Nombre')
                                                ->weight(FontWeight::Medium),

                                            Infolists\Components\TextEntry::make('client.contact_email')
                                                ->label('Email')
                                                ->icon('heroicon-o-envelope')
                                                ->iconPosition(IconPosition::Before),

                                            Infolists\Components\TextEntry::make('client.contact_phone')
                                                ->label('Teléfono')
                                                ->icon('heroicon-o-phone')
                                                ->iconPosition(IconPosition::Before),
                                        ]),

                                    Infolists\Components\Section::make('Asignación')
                                        ->icon('heroicon-o-user')
                                        ->iconColor('success')
                                        ->schema([
                                            Infolists\Components\TextEntry::make('agent.name')
                                                ->label('Agente asignado')
                                                ->placeholder('Sin asignar')
                                                ->weight(FontWeight::Medium),

                                            Infolists\Components\TextEntry::make('agent.email')
                                                ->label('Email')
                                                ->placeholder('N/A')
                                                ->icon('heroicon-o-envelope')
                                                ->iconPosition(IconPosition::Before),
                                        ]),
                                ]),

                            // Timeline
                            Section::make('Timeline')
                                ->icon('heroicon-o-clock')
                                ->iconColor('info')
                                ->collapsible()
                                ->collapsed(false)
                                ->schema([
                                    Livewire::make(\App\Livewire\TicketTimeline::class, [
                                        'ticket' => $this->record,
                                    ]),
                                ]),
                        ])->columnSpan(2),

                        // Columna derecha - Conversación completa (1/3 del ancho)
                        Infolists\Components\Group::make([
                            // Conversación - Ocupa todo el espacio vertical
                            Infolists\Components\Section::make('Conversación')
                                ->icon('heroicon-o-chat-bubble-left-right')
                                ->iconColor('warning')
                                ->collapsible()
                                ->collapsed(false)
                                ->extraAttributes([
                                    'class' => 'h-screen sticky top-0',
                                    'style' => 'min-height: 80vh; max-height: 90vh;',
                                ])
                                ->schema([
                                    Infolists\Components\ViewEntry::make('conversation')
                                        ->view('filament.resources.ticket-resource.pages.ticket-conversation')
                                        ->extraAttributes([
                                            'class' => 'overflow-y-auto',
                                            'style' => 'max-height: 75vh;',
                                        ]),
                                ]),
                        ])->columnSpan(1),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
