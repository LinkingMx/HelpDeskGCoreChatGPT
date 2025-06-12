<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Activos';

    protected static ?string $navigationLabel = 'Marcas';

    protected static ?string $label = 'Marca';

    protected static ?string $pluralLabel = 'Marcas';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Marca')
                    ->description('Registra las marcas de los equipos y activos')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la marca')
                            ->placeholder('Ej: HP, Dell, Lenovo, Apple, Samsung...')
                            ->helperText('💡 **Tip:** Usa el nombre oficial de la marca para facilitar la búsqueda.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->suffixIcon('heroicon-o-building-office'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Marca')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->description('Nombre de la marca'),

                Tables\Columns\TextColumn::make('assets_count')
                    ->label('Activos')
                    ->counts('assets')
                    ->icon('heroicon-o-computer-desktop')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->description('Activos registrados'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description('Cuándo se registró'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_assets')
                    ->label('Con activos')
                    ->placeholder('Todas las marcas')
                    ->trueLabel('Solo con activos')
                    ->falseLabel('Sin activos')
                    ->queries(
                        true: fn ($query) => $query->whereHas('assets'),
                        false: fn ($query) => $query->whereDoesntHave('assets'),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar marcas seleccionadas')
                        ->modalDescription('¿Estás seguro de que quieres eliminar las marcas seleccionadas? Los activos asociados quedarán sin marca asignada.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No hay marcas registradas')
            ->emptyStateDescription('Comienza registrando la primera marca para organizar tus activos por fabricante.')
            ->emptyStateIcon('heroicon-o-tag')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar primera marca')
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Activos' => $record->assets_count ?? $record->assets()->count(),
            'Registrada' => $record->created_at?->format('d/m/Y'),
        ];
    }
}
