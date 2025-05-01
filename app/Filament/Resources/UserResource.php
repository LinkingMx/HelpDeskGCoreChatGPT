<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Catalogos';
    protected static ?string $navigationLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),
                    
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpan(1),
                    
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                    ->columnSpanFull(),
                    
                Forms\Components\Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->required()
                    ->columnSpanFull()
                    ->live(),
                    
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name')
                    ->required(fn (Forms\Get $get): bool => 
                        collect($get('roles'))->contains(fn ($roleId) => 
                            Role::find($roleId)?->name === 'user'
                        )
                    )
                    ->visible(fn (Forms\Get $get): bool => 
                        collect($get('roles'))->contains(fn ($roleId) => 
                            Role::find($roleId)?->name === 'user'
                        )
                    )
                    ->columnSpanFull(),
                    
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(fn (Forms\Get $get): bool => 
                        collect($get('roles'))->contains(fn ($roleId) => 
                            Role::find($roleId)?->name === 'agent'
                        )
                    )
                    ->visible(fn (Forms\Get $get): bool => 
                        collect($get('roles'))->contains(fn ($roleId) => 
                            Role::find($roleId)?->name === 'agent'
                        )
                    )
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                    
                Tables\Columns\ViewColumn::make('roles')
                    ->view('filament.tables.columns.user-role-badges'),
                    
                Tables\Columns\TextColumn::make('client.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(['class' => 'hidden md:table-cell']),
                    
                Tables\Columns\TextColumn::make('department.name')
                    ->toggleable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple(),
                    
                Tables\Filters\SelectFilter::make('client')
                    ->relationship('client', 'name'),
                    
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->recordClasses(fn (User $record) => 'md:table-row')
            ->striped();
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
