<?php

namespace App\Filament\Resources\AssetResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
                    ->label('Tamaño'),

                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('Subido por')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();
                        if (isset($data['file_path'])) {
                            $data['file_name'] = basename($data['file_path']);
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record): string => asset('storage/'.$record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Sin adjuntos')
            ->emptyStateDescription('No hay archivos adjuntos para este activo.')
            ->emptyStateIcon('heroicon-o-document');
    }
}
