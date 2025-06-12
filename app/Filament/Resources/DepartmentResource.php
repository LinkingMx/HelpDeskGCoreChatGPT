<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalogos';

    protected static ?string $label = 'Departamento';

    protected static ?string $pluralLabel = 'Departamentos';

    protected static ?string $navigationLabel = 'Departamentos';

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'super_admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')

                    ->schema([

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del departamento')
                            ->placeholder('Ej: Sistemas e Informática, Mantenimiento y Facilities, Recursos Humanos...')
                            ->hint('Máximo 255 caracteres')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->suffixIcon('heroicon-o-tag'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Departamento')
                    ->icon('heroicon-o-building-office-2')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tickets_count')
                    ->label('Tickets Asignados')
                    ->counts('tickets')
                    ->icon('heroicon-o-ticket')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime('d/m/Y H:i')
                    ->description('Cuándo se creó el departamento')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última modificación')
                    ->dateTime('d/m/Y H:i')
                    ->description('Última vez que se editó')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_tickets')
                    ->label('Con tickets asignados')
                    ->placeholder('Todos los departamentos')
                    ->trueLabel('Solo con tickets')
                    ->falseLabel('Sin tickets asignados')
                    ->queries(
                        true: fn ($query) => $query->has('tickets'),
                        false: fn ($query) => $query->doesntHave('tickets'),
                    ),
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
                        ->modalHeading('Eliminar departamentos seleccionados')
                        ->modalDescription('¿Estás seguro de que quieres eliminar los departamentos seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No hay departamentos configurados')
            ->emptyStateDescription('Comienza creando el primer departamento para organizar el soporte técnico de tu empresa.')
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear primer departamento')
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'view' => Pages\ViewDepartment::route('/{record}'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
