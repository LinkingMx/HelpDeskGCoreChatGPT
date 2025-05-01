<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketCategoryResource\Pages;
use App\Models\TicketCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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
                // Name field - required
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                // Icon field with placeholder
                Forms\Components\TextInput::make('icon')
                    ->placeholder('heroicon-o-briefcase')
                    ->maxLength(255),
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
                    ->sortable(),
                
                // Name column
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                
                // Icon column that displays the actual icon
                Tables\Columns\IconColumn::make('icon')
                    ->icon(fn ($record): string => $record->icon ?? 'heroicon-o-question-mark-circle'),
                
                // Created at timestamp, toggleable
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                // No filters specified in requirements
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // Stack layout for mobile
            ->recordClasses(fn ($record) => 'md:table-row')
            ->defaultSort('id');
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
