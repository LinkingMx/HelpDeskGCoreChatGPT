<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketStatusResource\Pages;
use App\Models\TicketStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class TicketStatusResource extends Resource
{
    protected static ?string $model = TicketStatus::class;

    // Custom icon for the navigation
    protected static ?string $navigationIcon = 'heroicon-o-tag';

    // Set navigation group to "Catalogs"
    protected static ?string $navigationGroup = 'Config Tickets';

    // Custom navigation label "Statuses" instead of "Ticket Statuses"
    protected static ?string $navigationLabel = 'Estatus de tickets';

    protected static ?string $label = 'Estatus de ticket';

    protected static ?string $pluralLabel = 'Estatus de tickets';

    /**
     * Check if the current user can access this resource
     * Only admins and super admins can access this resource
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Define the form fields for creating and editing ticket statuses
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Estado')
                    ->description('Configure el estado del ticket con su nombre y color distintivo')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Estado')
                            ->placeholder('Ej: Abierto, En Progreso, Resuelto...')
                            ->required()
                            ->maxLength(40)
                            ->live(onBlur: true)
                            ->suffixIcon('heroicon-o-tag')
                            ->helperText('Máximo 40 caracteres. Este nombre aparecerá en todos los tickets.')
                            ->unique(TicketStatus::class, 'name', ignoreRecord: true)
                            ->rules([
                                'regex:/^[a-zA-ZÀ-ÿñÑ\s\-\/]+$/',
                                'min:2',
                            ])
                            ->validationMessages([
                                'unique' => 'Ya existe un estado con este nombre.',
                                'regex' => 'El nombre solo puede contener letras, espacios y guiones.',
                                'min' => 'El nombre debe tener al menos 2 caracteres.',
                            ]),

                        Forms\Components\Select::make('color')
                            ->label('Color del Badge')
                            ->options([
                                'primary' => 'Azul (Primary)',
                                'secondary' => 'Gris (Secondary)',
                                'success' => 'Verde (Success)',
                                'warning' => 'Amarillo (Warning)',
                                'danger' => 'Rojo (Danger)',
                                'info' => 'Azul Claro (Info)',
                            ])
                            ->required()
                            ->default('primary')
                            ->live()
                            ->suffixIcon('heroicon-o-swatch')
                            ->helperText('Selecciona el color que mejor represente este estado. Los colores ayudan a identificar rápidamente el estado del ticket.'),

                        Forms\Components\Placeholder::make('preview')
                            ->label('Vista Previa del Estado')
                            ->content(function (Forms\Get $get): \Illuminate\View\View {
                                $name = $get('name') ?: 'Nombre del Estado';
                                $color = $get('color') ?: 'primary';

                                return view('filament.components.status-preview', [
                                    'name' => $name,
                                    'color' => $color,
                                ]);
                            })
                            ->visible(fn (Forms\Get $get): bool => filled($get('name')) || filled($get('color')))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Configuración Adicional')
                    ->description('Opciones avanzadas para el comportamiento del estado')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Estado Activo')
                            ->helperText('Solo los estados activos aparecerán disponibles para asignar a tickets.')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('is_final')
                            ->label('Estado Final')
                            ->helperText('Los estados finales indican que el ticket ha sido completamente resuelto.')
                            ->default(false)
                            ->inline(false),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción (Opcional)')
                            ->placeholder('Describe cuándo usar este estado...')
                            ->maxLength(255)
                            ->rows(3)
                            ->helperText('Descripción interna para ayudar al equipo a entender cuándo usar este estado.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Define the table structure for displaying ticket statuses
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ID column, sortable
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),

                // Name column, searchable
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre del Estado')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->badge()
                    ->color(fn ($record): string => $record->color),

                // Active status
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                // Final status
                Tables\Columns\IconColumn::make('is_final')
                    ->label('Estado Final')
                    ->boolean()
                    ->trueIcon('heroicon-o-flag')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),

                // Description with truncation
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->placeholder('Sin descripción')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Created at timestamp, sortable and toggleable
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),

                // Updated at timestamp
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estados Activos')
                    ->placeholder('Todos los estados')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),

                Tables\Filters\TernaryFilter::make('is_final')
                    ->label('Estados Finales')
                    ->placeholder('Todos los estados')
                    ->trueLabel('Solo finales')
                    ->falseLabel('Solo no finales'),

                Tables\Filters\SelectFilter::make('color')
                    ->label('Color')
                    ->options([
                        'primary' => 'Azul (Primary)',
                        'secondary' => 'Gris (Secondary)',
                        'success' => 'Verde (Success)',
                        'warning' => 'Amarillo (Warning)',
                        'danger' => 'Rojo (Danger)',
                        'info' => 'Azul Claro (Info)',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('id')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * Define the resource's available pages
     */
    public static function getRelations(): array
    {
        return [
            // No relationships specified in the requirements
        ];
    }

    /**
     * Configure resource pages
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketStatuses::route('/'),
            'create' => Pages\CreateTicketStatus::route('/create'),
            'edit' => Pages\EditTicketStatus::route('/{record}/edit'),
        ];
    }
}
