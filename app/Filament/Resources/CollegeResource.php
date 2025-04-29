<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollegeResource\Pages;
use App\Filament\Resources\CollegeResource\RelationManagers;
use App\Filament\Resources\CollegeResource\RelationManagers\ProgramsRelationManager;
use App\Models\College;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use stdClass;

class CollegeResource extends Resource
{
    protected static ?string $model = College::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Colleges';

    protected static ?string $breadcrumb = 'Colleges';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('college')
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['onInput' => 'this.value = this.value.toUpperCase()'])
                            ->unique(ignoreRecord: true),
                        // Forms\Components\Repeater::make('programs')
                        //     ->relationship()
                        //     ->reorderable()
                        //     ->collapsible()
                        //     ->columnSpan('full')
                        //     ->schema([
                        //         Forms\Components\TextInput::make('program')
                        //             ->required()
                        //             ->maxLength(255)
                        //             ->extraInputAttributes(['onInput' => 'this.value = this.value.toUpperCase()'])
                        //             ->distinct(),
                        //         Forms\Components\Repeater::make('yearlevels')
                        //             ->relationship()
                        //             ->reorderable()
                        //             ->collapsible()
                        //             ->columnSpan('full')
                        //             ->schema([
                        //                 TextInput::make('yearlevel')
                        //                     ->numeric()
                        //                     ->minValue(1)
                        //                     ->maxValue(4)
                        //                     ->required()
                        //                     ->distinct(),
                        //                     Forms\Components\Repeater::make('yearlevelpayments')
                        //                         ->relationship()
                        //                         ->reorderable()
                        //                         ->collapsible()
                        //                         ->columnSpan('full')
                        //                         ->schema([
                        //                             TextInput::make('amount')
                        //                                 ->mask(RawJs::make('$money($input)'))
                        //                                 ->stripCharacters(',')
                        //                                 ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/[^\d.]/g, "").replace(/(\..*?)\.+/g, "$1").replace(/\B(?=(\d{3})+(?!\d))/g, ",")'])
                        //                                 ->numeric()
                        //                                 ->prefixIcon('heroicon-m-peso-symbol')
                        //                                 ->required(),
                        //                             TextInput::make('description')
                        //                                 ->required()
                        //                                 ->minLength(2)
                        //                                 ->maxLength(60)
                        //                                 ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/\\b\\w/g, char => char.toUpperCase())']),

                        //                             ])
                        //                             ->columns(2),
                        //                             ])
                        //                 ->columns(1),
                        //     ])
                        //     ->columns(1),
                    ]),
            ]);
    }

    // public static function canCreate(): bool
    // {
    //     return false;
    // }

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
                Tables\Columns\TextColumn::make('college')
                    ->weight(FontWeight::Bold)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('programs.program')
                    ->listWithLineBreaks()
                    ->sortable()
                    ->searchable(),
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
                RelationManagerAction::make('programs-relation-manager')
                    ->label('Add/View program')
                    ->icon('heroicon-m-academic-cap')
                    ->color('success')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalHeading('')
                    ->relationManager(ProgramsRelationManager::make()),
                Tables\Actions\EditAction::make()
                    ->color('warning')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('College updated successfully!')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->color('success')
                                ->icon('heroicon-o-check-circle')
                                ->title('Colleges deleted successfully!')
                        ),
                ]),
            ])
            ->emptyStateHeading('No records found');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProgramsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColleges::route('/'),
            'create' => Pages\CreateCollege::route('/create'),
            'edit' => Pages\EditCollege::route('/{record}/edit'),
        ];
    }
}
