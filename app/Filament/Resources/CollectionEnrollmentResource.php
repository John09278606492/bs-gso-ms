<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionEnrollmentResource\Pages;
use App\Models\Collection;
use App\Models\CollectionEnrollment;
use App\Models\College;
use App\Models\Program;
use App\Models\Schoolyear;
use App\Models\Yearlevel;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Filament\Tables\Contracts\HasTable;
use stdClass;

class CollectionEnrollmentResource extends Resource
{
    protected static ?string $model = CollectionEnrollment::class;

    protected static ?string $navigationIcon = 'heroicon-m-rectangle-stack';

    protected static ?string $breadcrumb = 'Collection Records';

    protected static ?string $navigationLabel = 'Collection Records';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            //
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // 🛠️ Add this to only show records where updated_at and collection_status are not null
                $query
                    ->whereNotNull('updated_at')
                    ->whereNotNull('collection_status');
            })
            ->columns([
                TextColumn::make('No')->state(
                    static function (HasTable $livewire, stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    }
                ),
                Tables\Columns\TextColumn::make('enrollment.stud.studentidn')
                    ->label('Student IDN')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment.stud.fullname')
                    ->label('Student Name')
                    ->sortable(['lastname', 'firstname', 'middlename'])
                    ->searchable([
                        'lastname',
                        'firstname',
                        'middlename',
                    ]),
                Tables\Columns\TextColumn::make('enrollment.college.college')
                    ->label('College')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment.program.program')
                    ->label('Program')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment.yearlevel.yearlevel')
                    ->label('Year Level')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment.schoolyear.schoolyear')
                    ->label('School Year')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('collection.description')
                    ->label('Category Name')
                    ->formatStateUsing(function ($state, $record) {
                        return 'Semester ' . ($record->collection->semester->semester ?? '-') . ': '
                            . ($state ?? '-');
                    })
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('collection_status')
                    ->label('Collection Status')
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Date/Time Paid')
                    ->dateTime('M d, Y - h:i a')
                    ->sortable(),
                Tables\Columns\TextColumn::make('collection.amount')
                    ->label('Amount')
                    ->money('PHP', locale: 'en_PH')
                    // ->summarize([
                    //     Sum::make()
                    //         ->money('PHP')
                    //         ->label('Total Amount'),
                    // ])
                    ->sortable(),
            ])
            ->pluralModelLabel('Pages')
            ->defaultSort('updated_at', 'desc')
            ->recordUrl(false)
            ->filters([
                DateRangeFilter::make('updated_at')
                    ->label('Date Range')
                    ->defaultToday(),
                Filter::make('filters')
                    ->form([
                        Select::make('collection_id')
                            ->label('Category Name')
                            ->options(
                                Collection::query()
                                    ->join('semesters', 'collections.semester_id', '=', 'semesters.id') // Join to get semester name
                                    ->selectRaw("
                                        collections.id,
                                        CONCAT('Semester ', semesters.semester, ': ', collections.description, ' - ₱',
                                        IFNULL(FORMAT(collections.amount, 2), '0.00')) as name
                                    ")
                                    ->pluck('name', 'collections.id')
                            )
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->multiple(),

                        // Select::make('college_id')
                        //     ->label('College')
                        //     ->placeholder('All')
                        //     ->options(College::all()->pluck('college', 'id'))
                        //     ->preload()
                        //     ->reactive()
                        //     ->afterStateUpdated(function (Set $set, $state, $livewire) {
                        //         $set('program_id', null);
                        //         $set('yearlevel_id', null);
                        //         $livewire->dispatch('refresh');
                        //     })
                        //     ->searchable(),
                        // Select::make('program_id')
                        //     ->label('Program')
                        //     ->placeholder('All')
                        //     ->options(fn(Get $get) => Program::query()
                        //         ->where('college_id', $get('college_id'))
                        //         ->pluck('program', 'id'))
                        //     ->reactive()
                        //     ->afterStateUpdated(function (Set $set, $state, $livewire) {
                        //         $set('yearlevel_id', null);
                        //         $livewire->dispatch('refresh');
                        //     })
                        //     ->preload()
                        //     ->searchable(),
                        // Select::make('yearlevel_id')
                        //     ->label('Year Level')
                        //     ->placeholder('All')
                        //     ->options(fn(Get $get) => Yearlevel::query()
                        //         ->where('program_id', $get('program_id'))
                        //         ->pluck('yearlevel', 'id'))
                        //     ->reactive()
                        //     ->preload()
                        //     ->searchable()
                        //     ->afterStateUpdated(fn($livewire) => $livewire->dispatch('refresh')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                isset($data['collection_id']) && is_array($data['collection_id']) && count($data['collection_id']) > 0,
                                fn(Builder $query) =>
                                $query->whereIn('collection_id', $data['collection_id']) // Ensure array filtering
                            );
                        // ->when(
                        //     $data['college_id'] ?? null,
                        //     fn(Builder $query, $collegeId) => $query->whereHas('enrollment', function ($q) use ($collegeId) {
                        //         $q->where('college_id', $collegeId);
                        //     })
                        // )
                        // ->when(
                        //     $data['program_id'] ?? null,
                        //     fn(Builder $query, $programId) => $query->whereHas('enrollment', function ($q) use ($programId) {
                        //         $q->where('program_id', $programId);
                        //     })
                        // )
                        // ->when(
                        //     $data['yearlevel_id'] ?? null,
                        //     fn(Builder $query, $yearlevelId) => $query->whereHas('enrollment', function ($q) use ($yearlevelId) {
                        //         $q->where('yearlevel_id', $yearlevelId);
                        //     })
                        // );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (!empty($data['collection_id'])) {
                            $collections = Collection::with('semester')
                                ->whereIn('id', (array) $data['collection_id'])
                                ->get()
                                ->map(function ($collection) {
                                    return 'Semester ' . ($collection->semester->semester ?? '-') . ': ' . ($collection->description ?? '-');
                                })
                                ->toArray();

                            $indicators['collection_id'] = 'Catergory Name: ' . implode(', ', $collections);
                        }


                        // if (! empty($data['college_id'])) {
                        //     $indicators['college_id'] = 'College: ' . College::find($data['college_id'])->college ?? 'N/A';
                        // }

                        // if (! empty($data['program_id'])) {
                        //     $indicators['program_id'] = 'Program: ' . Program::find($data['program_id'])->program ?? 'N/A';
                        // }

                        // if (! empty($data['yearlevel_id'])) {
                        //     $indicators['yearlevel_id'] = 'Year Level: ' . Yearlevel::find($data['yearlevel_id'])->yearlevel ?? 'N/A';
                        // }

                        return $indicators;
                    })
                    ->columns(3)
                    ->columnSpan(3) // Adjust the column span as needed
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(4)
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListCollectionEnrollments::route('/'),
            'create' => Pages\CreateCollectionEnrollment::route('/create'),
            'edit' => Pages\EditCollectionEnrollment::route('/{record}/edit'),
        ];
    }
}
