<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewHireResource\Pages;
use App\Models\NewHire;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewHireResource extends Resource
{
    protected static ?string $model = NewHire::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $modelLabel = 'Nuevo Ingreso';

    protected static ?string $pluralModelLabel = 'Nuevos Ingresos';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Nuevos Ingresos';

    protected static ?string $slug = 'nuevos-ingresos';

    public static function getNavigationLabel(): string
    {
        return 'Nuevos Ingresos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Empleado')
                    ->schema([
                        Forms\Components\TextInput::make('employee_name')
                            ->label('Nombre del Empleado')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('employee_position')
                            ->label('Puesto del Empleado')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha de Ingreso')
                            ->required()
                            ->default(now())
                            ->helperText('Fecha en la que el empleado comenzará a trabajar'),

                        Forms\Components\Select::make('client_id')
                            ->label('Sucursal o Área')
                            ->relationship('client', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Sucursal o área donde será asignado el empleado'),

                        Forms\Components\TextInput::make('direct_supervisor')
                            ->label('Jefe Directo')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nombre del supervisor directo del nuevo empleado'),

                        Forms\Components\Toggle::make('is_replacement')
                            ->label('Es Reemplazo')
                            ->helperText('Activar si este empleado está reemplazando a alguien')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Equipos Requeridos')
                    ->schema([
                        Forms\Components\CheckboxList::make('required_asset_types')
                            ->label('Tipos de Activos Necesarios')
                            ->relationship('assetTypes', 'name')
                            ->columns(3)
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Selecciona los tipos de equipos que necesita el nuevo empleado. Usa la búsqueda para filtrar opciones y los botones para seleccionar/deseleccionar todo.')
                            ->gridDirection('row')
                            ->searchPrompt('Buscar tipos de activos...')
                            ->noSearchResultsMessage('No se encontraron tipos de activos.')
                            ->searchDebounce(300),

                        Forms\Components\Textarea::make('other_equipment')
                            ->label('Otros Equipos (Especificar)')
                            ->placeholder('Especifique otros equipos no listados en los tipos de activos...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Textarea::make('additional_comments')
                            ->label('Comentarios Adicionales')
                            ->placeholder('Instrucciones especiales, configuraciones específicas, etc...')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'in_progress' => 'En Progreso',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_name')
                    ->label('Empleado')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee_position')
                    ->label('Puesto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Fecha de Ingreso')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Sucursal/Área')
                    ->sortable(),

                Tables\Columns\TextColumn::make('direct_supervisor')
                    ->label('Jefe Directo')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_replacement')
                    ->label('Reemplazo')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'in_progress' => 'En Progreso',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    }),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Creado por')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Sucursal o Área')
                    ->relationship('client', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'in_progress' => 'En Progreso',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),

                Tables\Filters\TernaryFilter::make('is_replacement')
                    ->label('Es Reemplazo'),

                Tables\Filters\Filter::make('start_date')
                    ->label('Filtrar por fecha de ingreso')
                    ->form([
                        Forms\Components\DatePicker::make('start_date_from')
                            ->label('Fecha de ingreso desde'),
                        Forms\Components\DatePicker::make('start_date_until')
                            ->label('Fecha de ingreso hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['start_date_from'], fn ($query, $date) => $query->whereDate('start_date', '>=', $date))
                            ->when($data['start_date_until'], fn ($query, $date) => $query->whereDate('start_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->modalHeading('Eliminar nuevo ingreso')
                    ->modalDescription('¿Estás seguro de que deseas eliminar este registro de nuevo ingreso? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('¡Solicitud eliminada!')
                            ->body('La solicitud de nuevo ingreso ha sido eliminada correctamente.')
                            ->icon('heroicon-o-trash')
                            ->color('success')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar nuevos ingresos seleccionados')
                        ->modalDescription('¿Estás seguro de que deseas eliminar los registros seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->successNotification(
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('¡Solicitudes eliminadas!')
                                ->body('Las solicitudes seleccionadas han sido eliminadas correctamente.')
                                ->icon('heroicon-o-trash')
                                ->color('success')
                        ),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Sin nuevos ingresos')
            ->emptyStateDescription('No hay registros de nuevos ingresos creados aún.')
            ->emptyStateIcon('heroicon-o-user-plus');
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
            'index' => Pages\ListNewHires::route('/'),
            'create' => Pages\CreateNewHire::route('/create'),
            'edit' => Pages\EditNewHire::route('/{record}/edit'),
            'view' => Pages\ViewNewHire::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
