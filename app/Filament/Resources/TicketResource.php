<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use App\Models\User;
use App\Notifications\TicketAlert;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $recordTitleAttribute = 'subject';

    protected static ?string $navigationLabel = 'Tickets';

    protected static ?string $label = 'Ticket';

    protected static ?string $pluralLabel = 'Tickets';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // 1. super_admin: can see all tickets without exception
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // 2. agent: can see tickets from their assigned department
        if ($user->hasRole('agent')) {
            if ($user->department_id) {
                return $query->where('department_id', $user->department_id);
            }

            return $query->whereRaw('1 = 0'); // No department = no tickets
        }

        // 3. user role: can see tickets from their assigned client
        if ($user->hasRole('user')) {
            if ($user->client_id) {
                return $query->where('client_id', $user->client_id);
            }

            return $query->whereRaw('1 = 0'); // No client = no tickets
        }

        // default: no access to tickets
        return $query->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        $defaultStatus = TicketStatus::where('name', 'Open')->first()?->id;

        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->columnSpan(1),

                Forms\Components\Select::make('department_id') // Add department_id Select
                    ->label('Departamento')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->live() // Use live() instead of reactive() for Filament v3+
                    ->afterStateUpdated(function (Set $set) { // Use Set for type hinting
                        $set('category_id', null);
                        $set('agent_id', null); // Reset agent_id when department changes
                    })
                    ->columnSpan(1),

                Forms\Components\Select::make('category_id')
                    ->label('Categoria')
                    ->options(fn (Get $get): array => // Use Get for type hinting
                        TicketCategory::query()
                            ->where('department_id', $get('department_id'))
                            ->pluck('name', 'id')->all())
                    // ->disablePlaceholderUnlessFilled('department_id') // Remove this line
                    ->disabled(fn (Get $get): bool => ! $get('department_id')) // Add this line to disable the field
                    ->searchable()
                    ->required() // Make it required
                    ->columnSpan(1),

                Forms\Components\TextInput::make('subject')
                    ->label('Asunto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('description')
                    ->label('Descripción')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Placeholder::make('status_info')
                    ->label('Estado')
                    ->content('El ticket se creará con estado "Iniciado" automáticamente.')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->columnSpan(1),

                Forms\Components\Select::make('priority')
                    ->label('Prioridad')
                    ->options([
                        1 => 'Alta',
                        2 => 'Media',
                        3 => 'Baja',
                    ])
                    ->default(2)
                    ->columnSpan(1),

                Forms\Components\Select::make('status_id')
                    ->label('Estado')
                    ->relationship('status', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->hidden(fn (string $operation): bool => $operation === 'create') // Oculto en creación
                    ->disabled(fn (): bool => ! auth()->user()->hasRole('agent')) // Solo agentes pueden editarlo
                    ->columnSpan(1),

                /* … dentro de ->schema([...]) … */
                Forms\Components\Select::make('agent_id')
                    ->label('Agente')
                /* ─────────────────────────────────────────────
                1. Opciones = agentes del mismo departamento
                ───────────────────────────────────────────── */
                    ->options(function (Get $get): array {
                        $deptId = $get('department_id');

                        return $deptId
                            ? User::role('agent')
                                ->where('department_id', $deptId)
                                ->pluck('name', 'id')
                                ->all()
                            : [];
                    })
                    ->searchable()
                /* ─────────────────────────────────────────────
                2. Visibilidad y edición según rol
                    • super_admin, admin, agent  → visible y editable
                    • user                       → oculto por completo
                ───────────────────────────────────────────── */
                    ->visible(fn () => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'agent']))
                /* Si prefieres que el user lo vea “sólo lectura” en lugar de ocultarlo,
                cambia la línea anterior por:
                ->disabled(fn () => Auth::user()?->hasRole('user'))
                */
                /* ─────────────────────────────────────────────
                3. Deshabilitar cuando no hay departamento seleccionado
                ───────────────────────────────────────────── */
                    ->disabled(fn (Get $get): bool => blank($get('department_id')))
                    ->placeholder('Seleccione primero un departamento')
                    ->live()            // actualiza dinámicamente al cambiar departamento
                    ->columnSpan(1),
                /* … dentro de ->schema([...]) … */
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('sla')
                    ->label('')
                    /* ─ Icono dinámico ─ */
                    ->icon(function ($record) {
                        $completedStates = ['Completado'];   // agrega más si los usas

                        return in_array($record->status->name ?? '', $completedStates, true)
                            ? 'heroicon-o-check-circle'             // ✔️ cuando está completado
                            : 'heroicon-o-clock';                   // ⏰ en progreso
                    })
                    ->state(fn () => true)                           // siempre renderiza el ícono
                    /* ─ Color dinámico ─ */
                    ->color(function ($record) {
                        $completedStates = ['Completado'];

                        if (in_array($record->status->name ?? '', $completedStates, true)) {
                            return 'secondary';                       // verde para el check
                        }

                        $sla = (int) optional($record->category)->time ?: 24;
                        $created = $record->created_at instanceof Carbon
                            ? $record->created_at
                            : Carbon::parse($record->created_at);
                        $elapsed = max(0, $created->diffInRealHours());

                        return match (true) {
                            $elapsed < $sla => 'success',   // verde
                            $elapsed < $sla * 2 => 'warning',   // amarillo
                            default => 'danger',    // rojo
                        };
                    })
                    ->tooltip(function ($record) {
                        $sla = (int) optional($record->category)->time ?: 24;

                        $created = $record->created_at instanceof Carbon
                            ? $record->created_at
                            : Carbon::parse($record->created_at);

                        $elapsed = max(0, $created->diffInRealHours());

                        return "{$elapsed} / {$sla} h";
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function ($record) {
                        return $record->subject;
                    }),

                Tables\Columns\TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($record) => $record->status->color ?? 'gray'),

                Tables\Columns\TextColumn::make('priority')
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
                    }),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departamento')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('agent.name')
                    ->label('Agente')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn (string $state): string => date('d M Y', strtotime($state))),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('include_closed')
                    ->label('Estado de Tickets')
                    ->placeholder('Solo activos')
                    ->trueLabel('Incluir cerrados')
                    ->falseLabel('Solo activos')
                    ->default(false)
                    ->queries(
                        true: fn (Builder $query) => $query,  // Sin filtros adicionales = todos los tickets
                        false: fn (Builder $query) => $query->whereHas('status', fn (Builder $q) => $q->where('is_final', false)), // Solo activos
                        blank: fn (Builder $query) => $query->whereHas('status', fn (Builder $q) => $q->where('is_final', false)), // Default: solo activos
                    ),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->relationship('status', 'name'),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options([
                        1 => 'Alta',
                        2 => 'Media',
                        3 => 'Baja',
                    ]),

                Tables\Filters\SelectFilter::make('department')
                    ->label('Departamento')
                    ->relationship('department', 'name'),

                Tables\Filters\Filter::make('created_at')
                    ->label('Fecha de creación')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Acción para cerrar ticket
                Tables\Actions\Action::make('close_ticket')
                    ->label('Cerrar Ticket')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cerrar Ticket')
                    ->modalDescription('¿Estás seguro de que quieres cerrar este ticket? Solo tú podrás reabrirlo.')
                    ->modalSubmitActionLabel('Sí, cerrar ticket')
                    ->visible(function (Ticket $record): bool {
                        // Solo visible para el creador del ticket
                        // Solo si está en estado "Completado"
                        return auth()->id() === $record->user_id
                            && $record->status->name === 'Completado';
                    })
                    ->action(function (Ticket $record) {
                        $closedStatus = TicketStatus::where('name', 'Cerrado')->first();
                        $record->update(['status_id' => $closedStatus->id]);

                        // Notificar al agente asignado
                        if ($record->agent) {
                            $record->agent->notify(new TicketAlert(
                                $record,
                                'Ticket #'.$record->id.' cerrado',
                                'El ticket "'.$record->subject.'" ha sido cerrado por el cliente.'
                            ));
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Ticket cerrado exitosamente')
                            ->success()
                            ->send();
                    }),

                // Acción para reabrir ticket
                Tables\Actions\Action::make('reopen_ticket')
                    ->label('Reabrir Ticket')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Reabrir Ticket')
                    ->modalDescription('¿Estás seguro de que quieres reabrir este ticket?')
                    ->modalSubmitActionLabel('Sí, reabrir ticket')
                    ->visible(function (Ticket $record): bool {
                        // Solo visible para el creador del ticket
                        // Solo si está en estado "Cerrado"
                        return auth()->id() === $record->user_id
                            && $record->status->name === 'Cerrado';
                    })
                    ->action(function (Ticket $record) {
                        $completedStatus = TicketStatus::where('name', 'Completado')->first();
                        $record->update(['status_id' => $completedStatus->id]);

                        // Notificar al agente asignado
                        if ($record->agent) {
                            $record->agent->notify(new TicketAlert(
                                $record,
                                'Ticket #'.$record->id.' reabierto',
                                'El ticket "'.$record->subject.'" ha sido reabierto por el cliente.'
                            ));
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Ticket reabierto exitosamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->filtersFormColumns(2);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
