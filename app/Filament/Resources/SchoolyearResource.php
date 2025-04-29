<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolyearResource\Pages;
use App\Filament\Resources\SchoolyearResource\RelationManagers;
use App\Filament\Resources\SchoolyearResource\RelationManagers\SemestersRelationManager;
use App\Models\Schoolyear;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use stdClass;

class SchoolyearResource extends Resource
{
    protected static ?string $model = Schoolyear::class;

    protected static ?string $navigationIcon = 'heroicon-m-calendar-date-range';

    protected static ?string $navigationLabel = 'School Years';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $breadcrumb = 'School Years';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('schoolyear')
                                    ->required()
                                    ->readOnly()
                                    ->columnStart(1),
                            ]),
                        DatePicker::make('startDate')
                            ->native(false)
                            ->live()
                            ->required()
                            ->label('Select School Year Start Date')
                            ->before('endDate')
                            ->date()
                            ->disabledDates(function () {
                                // Fetch all existing school year date ranges
                                $conflicts = Schoolyear::all(['startDate', 'endDate']);
                                $disabledDates = [];

                                foreach ($conflicts as $conflict) {
                                    $start = \Carbon\Carbon::parse($conflict->startDate)->format('Y-m-d');
                                    $end = \Carbon\Carbon::parse($conflict->endDate)->format('Y-m-d');

                                    // Generate all dates within the range and disable them
                                    $period = \Carbon\CarbonPeriod::create($start, $end);
                                    foreach ($period as $date) {
                                        $disabledDates[] = $date->format('Y-m-d');
                                    }
                                }

                                return $disabledDates;
                            })
                            ->validationMessages([
                                'required' => 'Start date is required',
                                'date' => 'Please enter a valid date',
                                'before' => 'Start date must be before the end date',
                            ])
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $endDate = $get('endDate');
                                if ($state && $endDate) {
                                    $startYear = \Carbon\Carbon::parse($state)->format('Y');
                                    $endYear = \Carbon\Carbon::parse($endDate)->format('Y');
                                    $set('schoolyear', "$startYear - $endYear");
                                }
                            }),

                        DatePicker::make('endDate')
                            ->native(false)
                            ->live()
                            ->required()
                            ->label('Select School Year End Date')
                            ->after('startDate')
                            ->date()
                            ->disabledDates(function () {
                                // Fetch all existing school year date ranges
                                $conflicts = Schoolyear::all(['startDate', 'endDate']);
                                $disabledDates = [];

                                foreach ($conflicts as $conflict) {
                                    $start = \Carbon\Carbon::parse($conflict->startDate)->format('Y-m-d');
                                    $end = \Carbon\Carbon::parse($conflict->endDate)->format('Y-m-d');

                                    // Generate all dates within the range and disable them
                                    $period = \Carbon\CarbonPeriod::create($start, $end);
                                    foreach ($period as $date) {
                                        $disabledDates[] = $date->format('Y-m-d');
                                    }
                                }

                                return $disabledDates;
                            })
                            ->validationMessages([
                                'required' => 'End date is required',
                                'date' => 'Please enter a valid date',
                                'after' => 'End date must be after the start date',
                            ])
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $startDate = $get('startDate');
                                if ($state && $startDate) {
                                    $startYear = \Carbon\Carbon::parse($startDate)->format('Y');
                                    $endYear = \Carbon\Carbon::parse($state)->format('Y');
                                    $set('schoolyear', "$startYear - $endYear");
                                }
                            }),
                        // DatePicker::make('startDate')
                        //     ->live()
                        //     ->required()
                        //     ->label('Select School Year Start Date')
                        //     ->before('endDate')
                        //     ->date()
                        //     ->rules([
                        //         function ($record) {
                        //             return function ($attribute, $value, $fail) use ($record) {
                        //                 if (! $value) {
                        //                     return;
                        //                 }

                        //                 $date = \Carbon\Carbon::parse($value);
                        //                 $endDate = $record?->endDate;

                        //                 $existingStart = Schoolyear::where('startDate', $date)
                        //                     ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                        //                     ->first();

                        //                 if ($existingStart) {
                        //                     $fail("A school year already exists starting on this date ({$date->format('d/m/Y')})");

                        //                     return;
                        //                 }

                        //                 $dateInBetween = Schoolyear::where(function ($query) use ($date) {
                        //                     $query->where('startDate', '<=', $date)
                        //                         ->where('endDate', '>=', $date);
                        //                 })
                        //                     ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                        //                     ->first();

                        //                 if ($dateInBetween) {
                        //                     $fail("This date falls within an existing school year ({$dateInBetween->startDate->format('d/m/Y')} - {$dateInBetween->endDate->format('d/m/Y')})");

                        //                     return;
                        //                 }

                        //                 if ($endDate) {
                        //                     $overlapping = Schoolyear::where(function ($query) use ($date, $endDate, $record) {
                        //                         $query->where(function ($q) use ($date, $endDate) {
                        //                             $q->where(function ($subQ) use ($date, $endDate) {
                        //                                 $subQ->where('startDate', '<=', $endDate)
                        //                                     ->where('endDate', '>=', $date);
                        //                             });
                        //                         });

                        //                         if ($record) {
                        //                             $query->where('id', '!=', $record->id);
                        //                         }
                        //                     })->first();

                        //                     if ($overlapping) {
                        //                         $fail("This date range overlaps with an existing school year ({$overlapping->startDate->format('d/m/Y')} - {$overlapping->endDate->format('d/m/Y')})");
                        //                     }
                        //                 }
                        //             };
                        //         },
                        //     ])
                        //     ->validationMessages([
                        //         'required' => 'Start date is required',
                        //         'date' => 'Please enter a valid date',
                        //         'before' => 'Start date must be before end date',
                        //     ])
                        //     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        //         $endDate = $get('endDate');
                        //         if ($state && $endDate) {
                        //             $startYear = \Carbon\Carbon::parse($state)->format('Y');
                        //             $endYear = \Carbon\Carbon::parse($endDate)->format('Y');
                        //             $set('schoolyear', "$startYear - $endYear");
                        //         }
                        //     }),

                        // DatePicker::make('endDate')
                        //     ->live()
                        //     ->required()
                        //     ->label('Select School Year End Date')
                        //     ->after('startDate')
                        //     ->date()
                        //     ->rules([
                        //         function ($record) {
                        //             return function ($attribute, $value, $fail) use ($record) {
                        //                 if (! $value) {
                        //                     return;
                        //                 }

                        //                 $date = \Carbon\Carbon::parse($value);
                        //                 $startDate = $record?->startDate ?? request()->input('schoolyears.startDate');

                        //                 $existingEnd = Schoolyear::where('endDate', $date)
                        //                     ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                        //                     ->first();

                        //                 if ($existingEnd) {
                        //                     $fail("A school year already exists ending on this date ({$date->format('d/m/Y')})");

                        //                     return;
                        //                 }

                        //                 $dateInBetween = Schoolyear::where(function ($query) use ($date) {
                        //                     $query->where('startDate', '<=', $date)
                        //                         ->where('endDate', '>=', $date);
                        //                 })
                        //                     ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                        //                     ->first();

                        //                 if ($dateInBetween) {
                        //                     $fail("This date falls within an existing school year ({$dateInBetween->startDate->format('d/m/Y')} - {$dateInBetween->endDate->format('d/m/Y')})");

                        //                     return;
                        //                 }

                        //                 if ($startDate) {
                        //                     $startDate = \Carbon\Carbon::parse($startDate);

                        //                     $overlapping = Schoolyear::where(function ($query) use ($startDate, $date, $record) {
                        //                         $query->where(function ($q) use ($startDate, $date) {
                        //                             $q->where('startDate', '<=', $date)
                        //                                 ->where('endDate', '>=', $startDate);
                        //                         });

                        //                         if ($record) {
                        //                             $query->where('id', '!=', $record->id);
                        //                         }
                        //                     })->first();

                        //                     if ($overlapping) {
                        //                         $fail("This date range overlaps with an existing school year ({$overlapping->startDate->format('d/m/Y')} - {$overlapping->endDate->format('d/m/Y')})");
                        //                     }
                        //                 }
                        //             };
                        //         },
                        //     ])
                        //     ->validationMessages([
                        //         'required' => 'End date is required',
                        //         'date' => 'Please enter a valid date',
                        //         'after' => 'End date must be after start date',
                        //     ])
                        //     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        //         $startDate = $get('startDate');
                        //         if ($state && $startDate) {
                        //             $startYear = \Carbon\Carbon::parse($startDate)->format('Y');
                        //             $endYear = \Carbon\Carbon::parse($state)->format('Y');
                        //             $set('schoolyear', "$startYear - $endYear");
                        //         }
                        //     }),
                        Select::make('status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ]),
                    ]),
                // Section::make('Semester Section')
                //     ->icon('heroicon-m-academic-cap')
                //     ->schema([
                //         Repeater::make('semesters')
                //             ->relationship()
                //             ->schema([
                //                 TextInput::make('semester')
                //                     ->required()
                //                     ->numeric()
                //                     ->minValue(1)
                //                     ->maxValue(2)
                //                     ->distinct(),
                //                 Repeater::make('collections')
                //                     ->relationship()
                //                     ->schema([
                //                         TextInput::make('amount')
                //                             ->mask(RawJs::make('$money($input)'))
                //                             ->stripCharacters(',')
                //                             ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/[^\d.]/g, "").replace(/(\..*?)\.+/g, "$1").replace(/\B(?=(\d{3})+(?!\d))/g, ",")'])
                //                             ->numeric()
                //                             ->prefixIcon('heroicon-m-peso-symbol')
                //                             ->required(),
                //                         TextInput::make('description')
                //                             ->required()
                //                             ->minLength(2)
                //                             ->maxLength(60)
                //                             ->extraInputAttributes(['onInput' => 'this.value = this.value.replace(/\\b\\w/g, char => char.toUpperCase())']),
                //                     ])
                //                     ->columns(2),
                //             ])
                //             ->columns(1),
                //     ]),
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
                TextColumn::make('schoolyear')
                    ->label('School Year')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('startDate')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('endDate')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('semesters.semester')
                    ->badge(),
                TextColumn::make('total_collection')
                    ->label('Total Fees')
                    ->money('PHP'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    }),
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
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                RelationManagerAction::make('semesters-relation-manager')
                    ->label('Add/View semester')
                    ->icon('heroicon-m-academic-cap')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalHeading('')
                    ->color('success')
                    ->relationManager(SemestersRelationManager::make()),
                Tables\Actions\EditAction::make()
                    ->color('warning')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->title('School Year udpated successfully!')
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
                                ->title('School Years deleted successfully!')
                        ),
                ]),
            ])
            ->emptyStateHeading('No records found');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SemestersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolyears::route('/'),
            'create' => Pages\CreateSchoolyear::route('/create'),
            'edit' => Pages\EditSchoolyear::route('/{record}/edit'),
        ];
    }
}
