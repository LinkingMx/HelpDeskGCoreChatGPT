<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Filament\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationGroup = 'Activos';

    protected static ?string $modelLabel = 'Activo';

    protected static ?string $pluralModelLabel = 'Activos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        Forms\Components\TextInput::make('asset_tag')
                            ->label('Etiqueta de Activo')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('serial_number')
                            ->label('Número de Serie')
                            ->unique(ignoreRecord: true),
                    ])->columns(3),

                Forms\Components\Section::make('Detalles del Producto')
                    ->schema([
                        Forms\Components\TextInput::make('model')
                            ->label('Modelo'),
                        Forms\Components\TextInput::make('supplier')
                            ->label('Proveedor'),
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Número de Factura'),
                    ])->columns(3),

                Forms\Components\Section::make('Clasificación')
                    ->schema([
                        Forms\Components\Select::make('asset_type_id')
                            ->label('Tipo de Activo')
                            ->relationship('assetType', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('brand_id')
                            ->label('Marca')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de la Marca')
                                    ->required()
                                    ->unique(),
                            ]),
                        Forms\Components\Select::make('asset_status_id')
                            ->label('Estado')
                            ->relationship('assetStatus', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('client_id')
                            ->label('Ubicación (Cliente)')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Asignación')
                    ->schema([
                        Forms\Components\Select::make('assigned_user_id')
                            ->label('Asignado a (Usuario)')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Seleccionar usuario...'),
                        Forms\Components\TextInput::make('assigned_to')
                            ->label('Asignado a (Texto libre)')
                            ->helperText('Use este campo si el responsable no está registrado como usuario del sistema'),
                    ])->columns(2),

                Forms\Components\Section::make('Información Financiera')
                    ->schema([
                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Fecha de Compra'),
                        Forms\Components\TextInput::make('purchase_cost')
                            ->label('Costo de Compra')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\DatePicker::make('warranty_expires_on')
                            ->label('Vencimiento de Garantía'),
                    ])->columns(3),

                Forms\Components\Section::make('Notas Adicionales')
                    ->schema([
                        Forms\Components\RichEditor::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_tag')
                    ->label('Etiqueta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Número de Serie')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assetType.name')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assetStatus.name')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Asignado a (Usuario)')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assigned_to')
                    ->label('Asignado a (Texto)')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('supplier')
                    ->label('Proveedor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_type_id')
                    ->label('Tipo')
                    ->relationship('assetType', 'name'),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Marca')
                    ->relationship('brand', 'name'),
                Tables\Filters\SelectFilter::make('asset_status_id')
                    ->label('Estado')
                    ->relationship('assetStatus', 'name'),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Ubicación')
                    ->relationship('client', 'name'),
                Tables\Filters\SelectFilter::make('assigned_user_id')
                    ->label('Asignado a (Usuario)')
                    ->relationship('assignedUser', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Etiqueta' => $record->asset_tag,
            'Modelo' => $record->model,
            'Tipo' => $record->assetType?->name,
            'Marca' => $record->brand?->name,
            'Estado' => $record->assetStatus?->name,
            'Asignado a' => $record->assignedUser?->name ?: $record->assigned_to,
        ];
    }
}
