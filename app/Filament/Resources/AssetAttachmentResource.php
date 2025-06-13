<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetAttachmentResource\Pages;
use App\Models\AssetAttachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssetAttachmentResource extends Resource
{
    protected static ?string $model = AssetAttachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Activos';

    protected static ?string $modelLabel = 'Adjunto de Activo';

    protected static ?string $pluralModelLabel = 'Adjuntos de Activos';

    // Ocultar del menú de navegación principal
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Archivo')
                    ->schema([
                        Forms\Components\Select::make('asset_id')
                            ->label('Activo')
                            ->relationship('asset', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Documento')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(500)
                            ->rows(3),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Archivo')
                            ->required()
                            ->disk('public')
                            ->directory('asset-attachments')
                            ->preserveFilenames()
                            ->acceptedFileTypes(['image/*', 'application/pdf', '.doc', '.docx', '.xls', '.xlsx', '.txt'])
                            ->maxSize(10240) // 10MB
                            ->downloadable(),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('Archivo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('file_size_human')
                    ->label('Tamaño')
                    ->sortable(),

                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('Subido por')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Subida')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asset_id')
                    ->label('Activo')
                    ->relationship('asset', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (AssetAttachment $record): string => asset('storage/'.$record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetAttachments::route('/'),
            'create' => Pages\CreateAssetAttachment::route('/create'),
            'edit' => Pages\EditAssetAttachment::route('/{record}/edit'),
        ];
    }
}
