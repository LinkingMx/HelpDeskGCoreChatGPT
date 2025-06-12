<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewDepartment extends ViewRecord
{
    protected static string $resource = DepartmentResource::class;

    protected static ?string $title = 'Ver Departamento';

    public function getSubheading(): ?string
    {
        return 'Información detallada y estadísticas del departamento de soporte técnico.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Departamento')
                ->icon('heroicon-o-pencil-square')
                ->color('primary'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información General')
                    ->description('Detalles básicos del departamento')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nombre del Departamento')
                            ->icon('heroicon-o-building-office-2')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Fecha de Creación')
                            ->icon('heroicon-o-calendar-days')
                            ->dateTime('d/m/Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Última Modificación')
                            ->icon('heroicon-o-clock')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Estadísticas de Tickets')
                    ->description('Resumen de la actividad del departamento')
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

                        Infolists\Components\TextEntry::make('agents_count')
                            ->label('Agentes Asignados')
                            ->icon('heroicon-o-users')
                            ->state(fn ($record) => $record->users()->count())
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Categorías de Tickets')
                    ->description('Tipos de incidencias que maneja este departamento')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('categories')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Categoría')
                                    ->icon('heroicon-o-folder')
                                    ->badge()
                                    ->color('gray'),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->categories()->count() === 0),

                Infolists\Components\Section::make('Agentes del Departamento')
                    ->description('Personal asignado a este departamento')
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
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->users()->count() === 0),
            ]);
    }
}
