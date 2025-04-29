<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use stdClass;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Admin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        // Forms\Components\TextInput::make('name')
                        //     ->required()
                        //     // ->hidden()
                        //     ->readOnly(true)
                        //     ->maxLength(255),

                        Forms\Components\TextInput::make('firstname')
                            ->maxLength(255)
                            ->required()
                            ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/\\b\\w/g, char => char.toUpperCase())'])
                            ->reactive(),

                        Forms\Components\TextInput::make('middlename')
                            ->maxLength(255)
                            ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/\\b\\w/g, char => char.toUpperCase())'])
                            ->reactive(),

                        Forms\Components\TextInput::make('lastname')
                            ->maxLength(255)
                            ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/\\b\\w/g, char => char.toUpperCase())'])
                            ->reactive(),
                        Forms\Components\TextInput::make('email1')
                            ->email()
                            ->label('Email Address (system login)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->label('Email Address (google login)')
                            ->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'guest' => 'Guest',
                            ]),
                        Forms\Components\TextInput::make('canId')
                            ->label('Unique IDN (for students only)')
                            ->unique(ignoreRecord: true)
                            ->numeric()
                            ->minValue(0)
                            ->minLength(6)
                            ->maxLength(15)
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('email_verified_at'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable(true)
                            ->maxLength(255)
                            ->dehydrated(fn($state) => filled($state)) // Only save if not empty
                            ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord) // Required only on creation
                            ->afterStateHydrated(fn($set) => $set('password', '')), // Keep the field empty on edit

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')->state(
                    static function (HasTable $livewire, stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    }
                ),
                Tables\Columns\TextColumn::make('canId')
                    ->label('Unique IDN (for students only)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Complete Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address (google login)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email1')
                    ->label('Email Address (system login)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('Student information updated successfully!')
                    )
                    ->failureNotification(
                        Notification::make()
                            ->title('Failed to update student information')
                            ->body('An error occurred while updating the student information.')
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Admin Accounts')
                        ->modalDescription('Are you sure you\'d like to delete these admin accounts? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete admin accounts')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->color('success')
                                ->icon('heroicon-o-check-circle')
                                ->title('Admin accounts deleted successfully!')
                        ),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
