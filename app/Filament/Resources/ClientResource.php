<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationGroup = 'Catalogos';
    protected static ?string $label = 'Cliente';
    protected static ?string $pluralLabel = 'Clientes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del cliente')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Nombre del contacto')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_email')
                            ->label('Email del contacto')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Teléfono del contacto')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\MarkdownEditor::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'md' => 2,
                        'default' => 1,
                    ]),
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
                
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contacto')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Email')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('Teléfono')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label('Tickets')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (string $state): string => date('d M Y', strtotime($state))),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_tickets')
                    ->label('Con tickets')
                    ->query(fn (Builder $query): Builder => $query->whereHas('tickets')),
                
                Tables\Filters\Filter::make('created_recently')
                    ->label('Creados recientemente')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
