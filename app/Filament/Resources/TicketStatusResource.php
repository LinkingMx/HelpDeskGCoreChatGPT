<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketStatusResource\Pages;
use App\Models\TicketStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketStatusResource extends Resource
{
    protected static ?string $model = TicketStatus::class;

    // Custom icon for the navigation
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    // Set navigation group to "Catalogs"
    protected static ?string $navigationGroup = 'Config Tickets';
    
    // Custom navigation label "Statuses" instead of "Ticket Statuses"
    protected static ?string $navigationLabel = 'Estatus de tickets';

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
                // Name field - required, with maximum length of 40 characters
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(40),
                
                // Color picker for status color with default blue value
                Forms\Components\ColorPicker::make('badge_color')
                    ->required()
                    ->default('#3b82f6'),
            ])
            ->columns(1); // Single column layout as requested
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
                    ->sortable(),
                
                // Name column, searchable
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                
                // Badge column that shows the color visually
                Tables\Columns\BadgeColumn::make('badge_color')
                    ->label('Color')
                    ->color(fn ($record): string => $record->badge_color),
                
                // Created at timestamp, sortable and toggleable
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                // No filters specified in the requirements
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Delete bulk action as requested
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // Stack layout for mobile screens
            ->recordClasses(fn ($record) => 'sm:table-row')
            ->defaultSort('id');
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
