<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $label = 'Usuario';

    protected static ?string $pluralLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ])->columns(2),

                Forms\Components\Section::make('Información Laboral')
                    ->schema([
                        Forms\Components\TextInput::make('position')
                            ->label('Puesto')
                            ->maxLength(255)
                            ->placeholder('Ej: Desarrollador, Gerente, Analista...'),
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                            ->placeholder(fn ($livewire) => $livewire instanceof Pages\EditUser
                                ? 'Deja en blanco para mantener la contraseña actual'
                                : 'Ingresa una contraseña segura')
                            ->helperText(fn ($livewire) => $livewire instanceof Pages\EditUser
                                ? '🔐 Solo completa este campo si deseas cambiar la contraseña'
                                : '🔐 Mínimo 8 caracteres, incluye letras y números'),
                    ])->columns(2),

                Forms\Components\Section::make('Asignaciones')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Seleccionar cliente (opcional)'),
                        Forms\Components\Select::make('department_id')
                            ->label('Departamento')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Seleccionar departamento (opcional)'),
                    ])->columns(2),

                Forms\Components\Section::make('Permisos')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->required()
                            ->columnSpanFull()
                            ->live(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Puesto')
                    ->searchable()
                    ->placeholder('No especificado'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\ViewColumn::make('roles')
                    ->label('Roles')
                    ->view('filament.tables.columns.user-role-badges'),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(['class' => 'hidden md:table-cell']),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departamento')
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple(),

                Tables\Filters\SelectFilter::make('client')
                    ->label('Cliente')
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
