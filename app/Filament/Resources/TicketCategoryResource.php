<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketCategoryResource\Pages;
use App\Models\Department;
use App\Models\TicketCategory; // Add this import
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketCategoryResource extends Resource
{
    protected static ?string $model = TicketCategory::class;

    // Set custom navigation icon
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    // Group under "Catalogs"
    protected static ?string $navigationGroup = 'Config Tickets';

    // Custom navigation label "Categories" instead of "Ticket Categories"
    protected static ?string $navigationLabel = 'Categoria de tickets';

    protected static ?string $label = 'Categoria de ticket';

    protected static ?string $pluralLabel = 'Categorias de tickets';

    /**
     * Check if the current user can access this resource
     * Restrict access to super_admin and admin roles
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Define the form fields for creating and editing ticket categories
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Categoría')
                    ->description('Configure los datos principales de la categoría de tickets')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la Categoría')
                            ->placeholder('Ej: Soporte Técnico, Hardware, Software...')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->suffixIcon('heroicon-o-clipboard-document-list')
                            ->helperText('Nombre descriptivo para identificar el tipo de tickets.')
                            ->unique(TicketCategory::class, 'name', ignoreRecord: true)
                            ->rules([
                                'regex:/^[a-zA-ZÀ-ÿñÑ\s\-\.\/0-9]+$/',
                                'min:3',
                            ])
                            ->validationMessages([
                                'unique' => 'Ya existe una categoría con este nombre.',
                                'regex' => 'El nombre solo puede contener letras, números, espacios y algunos signos de puntuación.',
                                'min' => 'El nombre debe tener al menos 3 caracteres.',
                            ]),

                        Forms\Components\Select::make('department_id')
                            ->label('Departamento Responsable')
                            ->placeholder('Selecciona el departamento encargado...')
                            ->relationship('department', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->suffixIcon('heroicon-o-building-office')
                            ->helperText('Departamento que se encargará de atender tickets de esta categoría.')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Configuración Visual y SLA')
                    ->description('Personaliza la apariencia y tiempos de respuesta esperados')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Forms\Components\TextInput::make('icon')
                            ->label('Icono (Heroicon)')
                            ->placeholder('heroicon-o-computer-desktop')
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->suffixIcon('heroicon-o-sparkles')
                            ->helperText('Nombre del icono de Heroicons (ej: heroicon-o-computer-desktop).')
                            ->rule('regex:/^heroicon-(o|s|m)-[a-z0-9\-]+$/')
                            ->validationMessages([
                                'regex' => 'El icono debe tener formato heroicon-o-nombre, heroicon-s-nombre o heroicon-m-nombre.',
                            ])
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('browse_icons')
                                    ->label('Buscar Iconos')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->color('primary')
                                    ->modalHeading('Explorar Iconos de Heroicons')
                                    ->modalDescription('Busca y selecciona iconos desde el sitio oficial de Heroicons')
                                    ->modalSubmitActionLabel('Cerrar')
                                    ->modalCancelAction(false)
                                    ->modalWidth('4xl')
                                    ->modalContent(view('filament.modals.heroicons-browser'))
                                    ->action(function () {
                                        // No action needed, just show the modal
                                    })
                            ),

                        Forms\Components\TextInput::make('time')
                            ->label('SLA - Tiempo de Resolución')
                            ->numeric()
                            ->suffix('horas')
                            ->minValue(1)
                            ->maxValue(168)
                            ->default(24)
                            ->required()
                            ->suffixIcon('heroicon-o-clock')
                            ->helperText('Tiempo máximo esperado para resolver tickets de esta categoría (en horas).')
                            ->rule('integer'),

                        Forms\Components\Placeholder::make('icon_preview')
                            ->label('Vista Previa del Icono')
                            ->content(function (Forms\Get $get): \Illuminate\View\View {
                                $icon = $get('icon') ?: 'heroicon-o-clipboard-document-list';
                                $name = $get('name') ?: 'Nombre de la Categoría';

                                return view('filament.components.category-preview', [
                                    'icon' => $icon,
                                    'name' => $name,
                                ]);
                            })
                            ->visible(fn (Forms\Get $get): bool => filled($get('icon')) || filled($get('name')) || true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Configuración Adicional')
                    ->description('Opciones avanzadas para el comportamiento de la categoría')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Categoría Activa')
                            ->helperText('Solo las categorías activas aparecerán disponibles para nuevos tickets.')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('requires_approval')
                            ->label('Requiere Aprobación')
                            ->helperText('Los tickets de esta categoría necesitarán aprobación antes de ser procesados.')
                            ->default(false)
                            ->inline(false),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción (Opcional)')
                            ->placeholder('Describe el tipo de problemas que abarca esta categoría...')
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText('Descripción interna para ayudar al equipo a entender cuándo usar esta categoría.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Define the table structure for displaying ticket categories
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ID column
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),

                // Name column with icon
                Tables\Columns\TextColumn::make('name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon(fn ($record): string => $record->icon ?: 'heroicon-o-clipboard-document-list'),

                // Department name column
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('Sin asignar'),

                // SLA time column
                Tables\Columns\TextColumn::make('time')
                    ->label('SLA')
                    ->sortable()
                    ->suffix(' hrs')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state <= 4 => 'danger',
                        $state <= 24 => 'warning',
                        $state <= 72 => 'success',
                        default => 'gray',
                    }),

                // Active status
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                // Requires approval status
                Tables\Columns\IconColumn::make('requires_approval')
                    ->label('Requiere Aprobación')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
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

                // Created at timestamp, toggleable
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
                    ->label('Categorías Activas')
                    ->placeholder('Todas las categorías')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),

                Tables\Filters\TernaryFilter::make('requires_approval')
                    ->label('Requiere Aprobación')
                    ->placeholder('Todas las categorías')
                    ->trueLabel('Solo con aprobación')
                    ->falseLabel('Solo sin aprobación'),

                Tables\Filters\SelectFilter::make('department')
                    ->label('Departamento')
                    ->relationship('department', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make('sla_time')
                    ->label('Tiempo SLA')
                    ->form([
                        Forms\Components\Select::make('sla_range')
                            ->label('Rango de SLA')
                            ->options([
                                'urgent' => 'Urgente (≤ 4 hrs)',
                                'normal' => 'Normal (5-24 hrs)',
                                'low' => 'Bajo (25-72 hrs)',
                                'extended' => 'Extendido (> 72 hrs)',
                            ])
                            ->placeholder('Seleccionar rango'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['sla_range'],
                            fn (Builder $query, $range): Builder => match ($range) {
                                'urgent' => $query->where('time', '<=', 4),
                                'normal' => $query->whereBetween('time', [5, 24]),
                                'low' => $query->whereBetween('time', [25, 72]),
                                'extended' => $query->where('time', '>', 72),
                            }
                        );
                    }),
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
     * Define relations (none specified in requirements)
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Configure resource pages
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketCategories::route('/'),
            'create' => Pages\CreateTicketCategory::route('/create'),
            'edit' => Pages\EditTicketCategory::route('/{record}/edit'),
        ];
    }
}
