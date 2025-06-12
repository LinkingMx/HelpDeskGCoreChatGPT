<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = '👁️ Ver Cliente';

    public function getSubheading(): ?string
    {
        return '📊 Información detallada y estadísticas del cliente registrado en el sistema.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Cliente')
                ->icon('heroicon-o-pencil-square')
                ->color('primary'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información General')
                    ->description('Datos básicos del cliente')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nombre del Cliente')
                            ->icon('heroicon-o-building-office')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Fecha de Registro')
                            ->icon('heroicon-o-calendar-days')
                            ->dateTime('d/m/Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Última Actualización')
                            ->icon('heroicon-o-clock')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Información de Contacto')
                    ->description('Datos del contacto principal')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('contact_name')
                            ->label('Nombre del Contacto')
                            ->icon('heroicon-o-user')
                            ->placeholder('Sin contacto asignado'),

                        Infolists\Components\TextEntry::make('contact_email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('Sin email registrado'),

                        Infolists\Components\TextEntry::make('contact_phone')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->placeholder('Sin teléfono registrado'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Estadísticas de Actividad')
                    ->description('Resumen de la actividad del cliente en el sistema')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Infolists\Components\TextEntry::make('tickets_count')
                            ->label('Total de Tickets')
                            ->icon('heroicon-o-ticket')
                            ->state(fn ($record) => $record->tickets()->count())
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('open_tickets_count')
                            ->label('Tickets Abiertos')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->state(fn ($record) => $record->tickets()->whereHas('status', fn ($q) => $q->where('name', '!=', 'Cerrado'))->count())
                            ->badge()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('closed_tickets_count')
                            ->label('Tickets Cerrados')
                            ->icon('heroicon-o-check-circle')
                            ->state(fn ($record) => $record->tickets()->whereHas('status', fn ($q) => $q->where('name', 'Cerrado'))->count())
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Usuarios Registrados')
                            ->icon('heroicon-o-users')
                            ->state(fn ($record) => $record->users()->count())
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Usuarios del Cliente')
                    ->description('Personal del cliente registrado en el sistema')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('users')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nombre')
                                    ->icon('heroicon-o-user'),

                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('roles.name')
                                    ->label('Rol')
                                    ->icon('heroicon-o-shield-check')
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Registrado')
                                    ->icon('heroicon-o-calendar')
                                    ->date('d/m/Y'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->users()->count() === 0),

                Infolists\Components\Section::make('Tickets Recientes')
                    ->description('Últimos tickets creados por este cliente')
                    ->icon('heroicon-o-ticket')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('recent_tickets')
                            ->label('')
                            ->state(fn ($record) => $record->tickets()->latest()->limit(5)->get())
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label('Título')
                                    ->icon('heroicon-o-document-text')
                                    ->limit(50),

                                Infolists\Components\TextEntry::make('status.name')
                                    ->label('Estado')
                                    ->icon('heroicon-o-flag')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'Abierto' => 'danger',
                                        'Iniciado' => 'warning',
                                        'En Progreso' => 'info',
                                        'Completado' => 'success',
                                        'Cerrado' => 'gray',
                                        default => 'gray'
                                    }),

                                Infolists\Components\TextEntry::make('priority.name')
                                    ->label('Prioridad')
                                    ->icon('heroicon-o-exclamation-triangle')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'Baja' => 'gray',
                                        'Media' => 'warning',
                                        'Alta' => 'danger',
                                        'Crítica' => 'danger',
                                        default => 'gray'
                                    }),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Creado')
                                    ->icon('heroicon-o-calendar')
                                    ->date('d/m/Y'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->tickets()->count() === 0),

                Infolists\Components\Section::make('Notas Internas')
                    ->description('Información adicional del cliente')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('')
                            ->markdown()
                            ->placeholder('Sin notas registradas')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => empty($record->notes)),
            ]);
    }
}
