<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
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

                Forms\Components\Section::make('Clasificación')
                    ->schema([
                        Forms\Components\Select::make('asset_type_id')
                            ->label('Tipo de Activo')
                            ->relationship('assetType', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
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
                    ])->columns(3),

                Forms\Components\Section::make('Asignación')
                    ->schema([
                        Forms\Components\TextInput::make('assigned_to')
                            ->label('Asignado a'),
                    ])->columns(1),

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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('assetType.name')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assetStatus.name')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assigned_to')
                    ->label('Asignado a')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_type_id')
                    ->label('Tipo')
                    ->relationship('assetType', 'name'),
                Tables\Filters\SelectFilter::make('asset_status_id')
                    ->label('Estado')
                    ->relationship('assetStatus', 'name'),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Ubicación')
                    ->relationship('client', 'name'),
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
            //
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
            'Tipo' => $record->assetType?->name,
            'Estado' => $record->assetStatus?->name,
        ];
    }
}
