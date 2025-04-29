<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Collection;
use App\Models\Semester;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class CauditWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?string $heading = 'Collection Audit';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Category Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable()
                    ->money('PHP'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Expected Collection')
                    ->sortable()
                    ->money('PHP')
                    ->summarize(
                        Sum::make()
                            ->label('Total:')
                            ->money('PHP')
                    ),

                Tables\Columns\TextColumn::make('total_collected_amount')
                    ->label('Total Collection')
                    ->sortable()
                    ->money('PHP')
                    ->summarize(
                        Sum::make()
                            ->label('Total: ')
                            ->money('PHP')
                    ),

                Tables\Columns\TextColumn::make('total_remaining_collection')
                    ->label('Total Receivables')
                    ->sortable()
                    ->money('PHP')
                    ->summarize(
                        Sum::make()
                            ->label('Total: ')
                            ->money('PHP')
                    ),

            ])
            ->pluralModelLabel('Pages')
            ->filters([
                Filter::make('collection_filter')
                    ->form([
                        Select::make('semester_id')
                            ->label('Semester')
                            ->options(fn(Get $get) => Semester::query()
                                ->where('schoolyear_id', $this->filters['schoolyear_id'] ?? null)
                                ->pluck('semester', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->multiple()
                            ->afterStateUpdated(function (Set $set, $state, $livewire) {
                                $set('collection_id', null);
                                $livewire->dispatch('refresh');
                            }),

                        Select::make('collection_id')
                            ->label('Collection')
                            ->options(fn(Get $get) => Collection::query()
                                ->whereIn('semester_id', (array) $get('semester_id'))
                                ->join('semesters', 'collections.semester_id', '=', 'semesters.id') // Join to get semester name
                                ->selectRaw("collections.id, CONCAT('Semester ', semesters.semester, ': ', collections.description, ' - ₱', IFNULL(FORMAT(collections.amount, 2), '0.00')) as name")
                                ->pluck('name', 'collections.id')) // Pluck formatted name with semester
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->multiple(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                !empty($this->filters['schoolyear_id']),
                                fn(Builder $query, $schoolyearId) =>
                                $query->whereHas('semester.schoolyear', fn($q) => $q->where('id', $schoolyearId))
                            )
                            ->when(
                                isset($data['semester_id']) && is_array($data['semester_id']) && count($data['semester_id']) > 0,
                                fn(Builder $query) =>
                                $query->whereIn('semester_id', $data['semester_id']) // Ensure array filtering
                            )
                            ->when(
                                isset($data['collection_id']) && is_array($data['collection_id']) && count($data['collection_id']) > 0,
                                fn(Builder $query) =>
                                $query->whereIn('collections.id', $data['collection_id']) // Ensure array filtering
                            );
                    })
                    ->columns(2)
                    ->columnSpan(2)
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(2);
    }

    private function getQuery(): Builder
    {
        $schoolyearId = $this->filters['schoolyear_id'] ?? null;

        if ($schoolyearId === '' || $schoolyearId === 'All') {
            $schoolyearId = null;
        }

        return Collection::query()
            ->selectRaw('
            collections.id,
            collections.description,
            collections.amount,
            COUNT(DISTINCT enrollments.id) * collections.amount AS total_amount,
            COUNT(CASE
                WHEN collection_enrollment.collection_status = "paid"
                THEN 1
            END) * collections.amount AS total_collected_amount,
            (COUNT(DISTINCT enrollments.id) * collections.amount)
                - (COUNT(CASE
                    WHEN collection_enrollment.collection_status = "paid"
                    THEN 1
                END) * collections.amount) AS total_remaining_collection,
            semesters.semester
        ')
            ->leftJoin('semesters', 'collections.semester_id', '=', 'semesters.id')
            ->leftJoin('collection_enrollment', 'collections.id', '=', 'collection_enrollment.collection_id')
            ->leftJoin('enrollments', 'collection_enrollment.enrollment_id', '=', 'enrollments.id')
            ->when($schoolyearId, function ($query) use ($schoolyearId) {
                $query->where('enrollments.schoolyear_id', $schoolyearId);
            })
            ->groupBy('collections.id', 'collections.description', 'collections.amount', 'semesters.semester');
    }


    // private function getQuery(): Builder
    // {
    //     $schoolyearId = $this->filters['schoolyear_id'] ?? null;

    //     if ($schoolyearId === '' || $schoolyearId === 'All') {
    //         $schoolyearId = null;
    //     }

    //     return Collection::query()
    //         ->selectRaw('
    //         collections.id,
    //         collections.description,
    //         collections.amount,
    //         -- Total expected amount (Amount per student * Number of students)
    //         COUNT(DISTINCT enrollments.id) * collections.amount as total_amount,
    //         -- Calculate total collected amount per student, distributing payments and subtracting refunds
    //         COALESCE(SUM(
    //             CASE
    //                 WHEN pays.status1 = "paid" THEN (pays.amount / student_collections.count)
    //                 WHEN pays.status1 = "refunded" THEN (-pays.amount / student_collections.count)
    //                 ELSE 0
    //             END
    //         ), 0) as total_collected_amount,
    //         -- Calculate total remaining collection
    //         (COUNT(DISTINCT enrollments.id) * collections.amount) - COALESCE(SUM(
    //             CASE
    //                 WHEN pays.status1 = "paid" THEN (pays.amount / student_collections.count)
    //                 WHEN pays.status1 = "refunded" THEN (-pays.amount / student_collections.count)
    //                 ELSE 0
    //             END
    //         ), 0) as total_remaining_collection,
    //         semesters.semester
    //     ')
    //         ->leftJoin('semesters', 'collections.semester_id', '=', 'semesters.id')
    //         ->leftJoin('collection_enrollment', 'collections.id', '=', 'collection_enrollment.collection_id')
    //         ->leftJoin('enrollments', 'collection_enrollment.enrollment_id', '=', 'enrollments.id')
    //         ->leftJoin('pays', 'enrollments.id', '=', 'pays.enrollment_id')
    //         ->leftJoinSub(
    //             DB::table('collection_enrollment')
    //                 ->selectRaw('enrollment_id, COUNT(collection_id) as count')
    //                 ->groupBy('enrollment_id'),
    //             'student_collections',
    //             'student_collections.enrollment_id',
    //             'enrollments.id'
    //         )
    //         ->groupBy('collections.id', 'collections.description', 'semesters.semester', 'collections.amount');
    // }
}
