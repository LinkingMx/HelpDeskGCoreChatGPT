<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketCategoryResource\Pages;
use App\Models\TicketCategory;
use App\Models\Department; // Add this import
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
                // Name field - required
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la categoria')
                    ->required()
                    ->maxLength(255),

                // Department relationship select - required
                Forms\Components\Select::make('department_id')
                    ->label('Departamento')
                    ->preload() // Preload departments
                    ->relationship('department', 'name') // Define relationship
                    ->required() // Make it required
                    ->searchable(), // Allow searching departments

                // Icon field with placeholder
                Forms\Components\TextInput::make('icon')
                    ->placeholder('heroicon-o-briefcase')
                    ->maxLength(255),
                    
                // SLA time field in hours
                Forms\Components\TextInput::make('time')
                    ->numeric()
                    ->label('SLA (hrs)')
                    ->minValue(1)
                    ->maxValue(168)
                    ->default(24)
                    ->required()
                    ->helperText('Max expected time to resolve this kind of ticket'),
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
                    ->sortable(),
                
                // Name column
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                // Department name column
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departamento')
                    ->searchable() // Allow searching by department name
                    ->sortable(), // Allow sorting by department name
                
                // SLA time column
                Tables\Columns\TextColumn::make('time')
                    ->label('SLA h')
                    ->sortable()
                    ->toggleable(),
                
                // Icon column that displays the actual icon
                Tables\Columns\IconColumn::make('icon')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon(fn ($record): string => $record->icon ?? 'heroicon-o-question-mark-circle'),
                
                // Created at timestamp, toggleable
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
