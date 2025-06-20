<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Clientes / Areas';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Catalogos';

    protected static ?string $label = 'Cliente';

    protected static ?string $pluralLabel = 'Clientes';

    protected static ?int $navigationSort = 1;

    /**
     * Define query scope based on user role
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // super_admin: can see all clients
        if ($user->hasRole(['super_admin', 'admin'])) {
            return $query;
        }

        // agent: can see all clients (they need to assign tickets to clients)
        if ($user->hasRole('agent')) {
            return $query;
        }

        // user role: only see their own client
        if ($user->hasRole('user') && $user->client_id) {
            return $query->where('id', $user->client_id);
        }

        // default: no access to clients
        return $query->whereRaw('1 = 0'); // This returns no results
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Información General')
                    ->description('Datos básicos del cliente')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del cliente / empresa')
                            ->placeholder('Ej: Mochomos MTY, CEDIS, Casa Socio...')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->suffixIcon('heroicon-o-building-office'),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Información de Contacto')
                    ->description('Datos de la persona responsable o contacto principal (campos opcionales)')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Nombre del contacto principal')
                            ->maxLength(255)
                            ->suffixIcon('heroicon-o-user')
                            ->default(''),

                        Forms\Components\TextInput::make('contact_email')
                            ->label('Email del contacto')
                            ->email()
                            ->maxLength(255)
                            ->suffixIcon('heroicon-o-envelope')
                            ->default(''),

                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Teléfono del contacto')
                            ->tel()
                            ->maxLength(255)
                            ->suffixIcon('heroicon-o-phone')
                            ->default(''),

                        Forms\Components\Textarea::make('address')
                            ->label('Dirección')
                            ->placeholder('Dirección completa del cliente/sucursal')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Campo opcional para registrar la ubicación física'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Notas Internas')
                    ->description('Información adicional para el equipo de soporte')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('notes')
                            ->label('Notas y observaciones')
                            ->placeholder('Escribe horarios de servicio, info adicional, etc.')
                            ->helperText('📝 **Nota:** Esta información solo es visible para el equipo de soporte y ayuda a brindar un mejor servicio.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Cliente / Empresa')
                    ->icon('heroicon-o-building-office')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contacto Principal')
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->placeholder('Sin contacto asignado')
                    ->formatStateUsing(fn ($state) => $state ?: 'Sin contacto asignado'),

                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Sin email registrado')
                    ->formatStateUsing(fn ($state) => $state ?: 'Sin email registrado'),

                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('Teléfono')
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->placeholder('Sin teléfono')
                    ->formatStateUsing(fn ($state) => $state ?: 'Sin teléfono')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('address')
                    ->label('Dirección')
                    ->icon('heroicon-o-map-pin')
                    ->searchable()
                    ->placeholder('Sin dirección registrada')
                    ->formatStateUsing(fn ($state) => $state ?: 'Sin dirección registrada')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->address)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tickets_count')
                    ->label('Tickets')
                    ->counts('tickets')
                    ->icon('heroicon-o-ticket')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->icon('heroicon-o-users')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->description('Cuándo se registró el cliente')
                    ->icon('heroicon-o-calendar-days')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_tickets')
                    ->label('Con tickets')
                    ->placeholder('Todos los clientes')
                    ->trueLabel('Solo con tickets')
                    ->falseLabel('Sin tickets')
                    ->queries(
                        true: fn ($query) => $query->whereHas('tickets'),
                        false: fn ($query) => $query->whereDoesntHave('tickets'),
                    ),

                Tables\Filters\TernaryFilter::make('has_users')
                    ->label('Con usuarios')
                    ->placeholder('Todos los clientes')
                    ->trueLabel('Solo con usuarios')
                    ->falseLabel('Sin usuarios')
                    ->queries(
                        true: fn ($query) => $query->whereHas('users'),
                        false: fn ($query) => $query->whereDoesntHave('users'),
                    ),

                Tables\Filters\Filter::make('created_recently')
                    ->label('Filtrar por fecha de registro')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde')
                            ->placeholder('Selecciona fecha inicial'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta')
                            ->placeholder('Selecciona fecha final'),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['from'] && ! $data['until']) {
                            return null;
                        }

                        if ($data['from'] && $data['until']) {
                            return 'Registrados entre: '.\Carbon\Carbon::parse($data['from'])->format('d/m/Y').' - '.\Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                        }

                        if ($data['from']) {
                            return 'Registrados desde: '.\Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                        }

                        return 'Registrados hasta: '.\Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver detalles'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar clientes seleccionados')
                        ->modalDescription('¿Estás seguro de que quieres eliminar los clientes seleccionados? Esta acción eliminará también sus usuarios y tickets asociados.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No hay clientes registrados')
            ->emptyStateDescription('Comienza registrando el primer cliente para organizar el sistema de soporte de tu empresa.')
            ->emptyStateIcon('heroicon-o-building-office')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar primer cliente')
                    ->icon('heroicon-o-plus'),
            ]);
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
