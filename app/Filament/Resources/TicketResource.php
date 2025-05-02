<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use App\Models\TicketCategory; // Add this import
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Components\Tab;
use Filament\Forms\Get; // Add this import
use Filament\Forms\Set; // Add this import
use Closure; // Add this import


class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $recordTitleAttribute = 'subject';
    protected static ?string $navigationLabel = 'Tickets';
    protected static ?string $label = 'Ticket';
    protected static ?string $pluralLabel = 'Tickets';

    
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = auth()->user();
        
        // super_admin & admin: no restrictions
        if ($user->hasRole(['super_admin', 'admin'])) {
            return $query;
        }
        
        // agent: show tickets assigned to them or in their department
        if ($user->hasRole('agent')) {
            return $query->where(function ($query) use ($user) {
                return $query->where('agent_id', $user->id)
                    ->orWhere('department_id', $user->department_id);
            });
        }
        
        // regular user: show only their tickets
        return $query->where('user_id', $user->id);
    }

    public static function form(Form $form): Form
    {
        $defaultStatus = TicketStatus::where('name', 'Open')->first()?->id;
        
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->columnSpan(1),
                    
                Forms\Components\Select::make('department_id') // Add department_id Select
                    ->label('Departamento')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->live() // Use live() instead of reactive() for Filament v3+
                    ->afterStateUpdated(function (Set $set) { // Use Set for type hinting
                        $set('category_id', null);
                        $set('agent_id', null); // Reset agent_id when department changes
                    })
                    ->columnSpan(1),

                Forms\Components\Select::make('category_id')
                    ->label('Categoria')
                    ->options(fn (Get $get): array => // Use Get for type hinting
                        TicketCategory::query()
                            ->where('department_id', $get('department_id'))
                            ->pluck('name','id')->all())
                    // ->disablePlaceholderUnlessFilled('department_id') // Remove this line
                    ->disabled(fn (Get $get): bool => !$get('department_id')) // Add this line to disable the field
                    ->searchable()
                    ->required() // Make it required
                    ->columnSpan(1),
                    
                Forms\Components\TextInput::make('subject')
                    ->label('Asunto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                    
                Forms\Components\RichEditor::make('description')
                    ->label('Descripción')
                    ->required()
                    ->columnSpanFull(),
                    
                Forms\Components\Select::make('priority')
                    ->label('Prioridad')
                    ->options([
                        1 => 'Alta',
                        2 => 'Media',
                        3 => 'Baja'
                    ])
                    ->default(2)
                    ->columnSpan(1),
                    
                Forms\Components\Select::make('status_id')
                    ->label('Estado')
                    ->relationship('status', 'name')
                    ->default($defaultStatus)
                    ->columnSpan(1),
                    
                Forms\Components\Select::make('agent_id')
                    ->label('Agente')
                    ->options(function (Get $get): array { // Use Get for type hinting
                        $deptId = $get('department_id');
                        return $deptId
                            ? User::role('agent')
                                ->where('department_id', $deptId)
                                ->pluck('name', 'id')
                                ->all()
                            : [];
                    })
                    ->searchable()
                    ->disabled(fn (Get $get): bool => !$get('department_id')) // Disable if no department selected
                    ->placeholder('Seleccione primero un departamento') // Add placeholder
                    ->live() // Needed for options to update dynamically
                    ->columnSpan(1),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id())
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function ($record) {
                        return $record->subject;
                    }),
                
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($record) => match($record->status->name ?? '') {
                        'Open' => 'primary',
                        'In Progress' => 'warning',
                        'Resolved' => 'success',
                        'Closed' => 'gray',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (int $state): string => match($state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'success',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (int $state): string => match($state) {
                        1 => 'Alta',
                        2 => 'Media',
                        3 => 'Baja',
                        default => 'Media',
                    }),
                
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departamento')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('agent.name')
                    ->label('Agente')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn (string $state): string => date('d M Y', strtotime($state))),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->relationship('status', 'name'),
                    
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options([
                        1 => 'Alta',
                        2 => 'Media',
                        3 => 'Baja'
                    ]),
                
                Tables\Filters\SelectFilter::make('department')
                    ->label('Departamento')
                    ->relationship('department', 'name'),
                    
                Tables\Filters\Filter::make('created_at')
                    ->label('Fecha de creación')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
