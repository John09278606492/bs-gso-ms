<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YearlevelResource\Pages;
use App\Filament\Resources\YearlevelResource\RelationManagers;
use App\Filament\Resources\YearlevelResource\RelationManagers\YearlevelpaymentsRelationManager;
use App\Models\Program;
use App\Models\Yearlevel;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Illuminate\Validation\Rules\Unique;
use stdClass;

class YearlevelResource extends Resource
{
    protected static ?string $model = Yearlevel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Year Levels';

    protected static ?string $breadcrumb = 'Year Levels';

    protected static ?string $navigationGroup = 'ACADEMIC PROFILE MANAGEMENT';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('program_id')
                            ->label('Program')
                            ->options(Program::all()->pluck('program', 'id'))
                            ->placeholder('Select a program')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('yearlevel')
                            ->label('Year Level')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(4)
                            ->unique(
                                modifyRuleUsing: function (Unique $rule, callable $get) {
                                    $yearlevel = $get('yearlevel');
                                    $program_id = $get('program_id');

                                    return $rule->where('program_id', $program_id)->where('yearlevel', $yearlevel);
                                },
                                ignoreRecord: true,
                            ),
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
                Tables\Columns\TextColumn::make('program.program')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('yearlevel')
                    ->label('Year Level')
                    ->weight(FontWeight::Bold)
                    ->sortable()
                    ->searchable(),
                // Tables\Columns\TextColumn::make('yearlevelpayments')
                //     ->label('Year Level Fee Types')
                //     ->formatStateUsing(function ($record) {
                //         return $record->yearlevelpayments
                //             ->map(fn($payment) => '₱' . number_format($payment->amount, 2) . " - {$payment->description}")
                //             ->join('<br>');
                //     })
                //     ->html(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y h:i a')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y h:i a')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // RelationManagerAction::make('yearlevelpayments-relation-manager')
                //     ->label('Add/View fee type')
                //     ->icon('heroicon-m-banknotes')
                //     ->color('success')
                //     ->modalSubmitAction(false)
                //     ->modalCancelActionLabel('Close')
                //     ->modalHeading('')
                //     ->relationManager(YearlevelpaymentsRelationManager::make()),
                Tables\Actions\EditAction::make()
                    ->color('warning')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('Year Level updated successfully!')
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
                                ->title('Year Levels deleted successfully!')
                        ),
                ]),
            ])
            ->emptyStateHeading('No records found');
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         RelationManagers\YearlevelpaymentsRelationManager::class,
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListYearlevels::route('/'),
            'create' => Pages\CreateYearlevel::route('/create'),
            'edit' => Pages\EditYearlevel::route('/{record}/edit'),
        ];
    }
}
