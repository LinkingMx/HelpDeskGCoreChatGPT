<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\IconPosition;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
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
                            
                        Infolists\Components\Section::make('Descripción')
                            ->icon('heroicon-o-document-text')
                            ->collapsible()
                            ->schema([
                                Infolists\Components\TextEntry::make('description')
                                    ->html()
                                    ->prose()
                                    ->extraAttributes([
                                        'class' => 'p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700'
                                    ]),
                        ])->columnSpanFull(),
                    ])
                    ->columns(2),
                
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
                            ->color(fn (int $state): string => match($state) {
                                1 => 'danger',
                                2 => 'warning',
                                3 => 'success',
                                default => 'warning',
                            })
                            ->formatStateUsing(fn (int $state): string => match($state) {
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
                    ->columns(4),
                
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
                            ])->columns(3),
                            
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
                            ])->columns(3),
                    ])
                    ->columnSpanFull(),
                    
                // Conversación
                Infolists\Components\Section::make('Conversación')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->iconColor('warning')
                    ->extraAttributes(['class' => 'mt-4'])
                    ->schema([
                        Infolists\Components\ViewEntry::make('conversation')
                            ->view('filament.resources.ticket-resource.pages.ticket-conversation')
                    ])
                    ->columnSpanFull(),
            ]);
    }
}