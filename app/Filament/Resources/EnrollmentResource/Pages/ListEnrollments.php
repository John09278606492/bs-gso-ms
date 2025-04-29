<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Exports\EnrollmentExporter;
use App\Filament\Imports\EnrollmentImporter;
use App\Filament\Resources\EnrollmentResource;
use App\Filament\Resources\EnrollmentResource\Widgets\TotalPayableWidget;
use App\Models\Enrollment;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Livewire\Attributes\On;
use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class ListEnrollments extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = EnrollmentResource::class;

    protected ?string $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(),
            // Action::make('export-to-excel')
            //     ->color('success')
            //     ->icon('heroicon-m-arrow-down-on-square-stack')
            //     ->label('Export to EXCEL')
            //     ->livewireClickHandlerEnabled()
            //     ->url(function () {
            //         $schoolYearId = $this->tableFilters['course_filter']['schoolyear_id'] ?? null;

            //         // Handle cases where schoolYearId is empty, a string, or an array
            //         if ($schoolYearId === null || $schoolYearId === "" || $schoolYearId === []) {
            //             return route('ALL.STUDENT.PAYMENT');
            //         } else {
            //             return route('EXPORT.STUDENT.PAYMENT', ['id' => (int) $schoolYearId]);
            //         }
            //     }),
            Action::make('export-to-excel')
                ->color('success')
                ->icon('heroicon-m-printer')
                ->label('Export to EXCEL')
                // ->action(function () {
                //     Notification::make()
                //         ->title('EXCEL Export in Progress')
                //         ->body('Your student payment information export is being processed. Please wait for a moment.')
                //         ->info()
                //         ->color('info')
                //         ->send();
                // })
                ->livewireClickHandlerEnabled()
                ->url(function () {
                    $filters = $this->tableFilters['course_filter'] ?? [];

                    return route('EXPORT.STUDENT.PAYMENT', array_filter([
                        'schoolyear_id' => $filters['schoolyear_id'] ?? null,
                        'college_id' => $filters['college_id'] ?? null,
                        'program_id' => $filters['program_id'] ?? null,
                        'yearlevel_id' => $filters['yearlevel_id'] ?? null,
                        'status' => $filters['status'] ?? null,
                    ]));
                }),
            // Action::make('export-to-pdf')
            //     ->color('danger')
            //     ->icon('heroicon-m-printer')
            //     ->label('Export to PDF')
            //     ->livewireClickHandlerEnabled()
            //     ->url(function () {
            //         $filters = $this->tableFilters['course_filter'] ?? [];

            //         return route('EXPORT.RECORDS', array_filter([
            //             'schoolyear_id' => $filters['schoolyear_id'] ?? null,
            //             'college_id' => $filters['college_id'] ?? null,
            //             'program_id' => $filters['program_id'] ?? null,
            //             'yearlevel_id' => $filters['yearlevel_id'] ?? null,
            //             'status' => $filters['status'] ?? null,
            //         ]));
            //     }),
            // ->url(fn () =>
            //     // Check if `schoolyear_id` exists in the filters. If it does, pass it, otherwise don't pass the id.
            //     $this->tableFilters['course_filter']['schoolyear_id']
            //         ? route('EXPORT.RECORDS', ['id' => $this->tableFilters['course_filter']['schoolyear_id']])
            //         : route('EXPORT.RECORDS.ALL') // No `id` passed if it's null
            // ),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return static::getResource()::getEloquentQuery();
    }

    public function getTitle(): string|Htmlable
    {
        return __('Student Payments');
    }

    public function getHeaderWidgets(): array
    {
        return [
            TotalPayableWidget::class,
        ];
    }

    // public function mount(): void
    // {
    //     parent::mount();

    //     // Retrieve applied filters
    //     $appliedFilters = $this->getTableFilters();

    //     // Log or use the filters
    //     Log::info('Filters applied:', $appliedFilters);
    // }

    // public function getTableFilters(): array
    // {
    //     return $this->table->getFilters();
    //

    // public function getTabs(): array
    // {
    //     return [
    //         'all' => Tab::make(),
    //         'unpaid' => Tab::make('Not Fully Paid')
    //             ->badgeColor('danger')
    //             ->badge(fn () => $this->getFilteredTableQuery()->clone()->where('status', null)->count())
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', NULL)),
    //         'paid' => Tab::make()
    //             ->label('Fully Paid')
    //             ->badgeColor('success')
    //             ->badge(fn () => $this->getFilteredTableQuery()->clone()->where('status', 'paid')->count())
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paid')),
    //     ];
    // }

    // #[On('refresh')]
    // public function getTabs(): array
    // {
    //     $schoolyearId = $this->tableFilters['course_filter']['schoolyear_id'] ?? null;

    //     // Get filter values dynamically
    //     $collegeId = $this->tableFilters['course_filter']['college_id'] ?? null;
    //     $programId = $this->tableFilters['course_filter']['program_id'] ?? null;
    //     $yearlevelId = $this->tableFilters['course_filter']['yearlevel_id'] ?? null;

    //     $baseQuery = Enrollment::query();

    //     // Apply filters dynamically
    //     $baseQuery
    //         ->when($schoolyearId, fn ($query) => $query->where('schoolyear_id', $schoolyearId))
    //         ->when($collegeId, fn ($query) => $query->where('college_id', $collegeId))
    //         ->when($programId, fn ($query) => $query->where('program_id', $programId))
    //         ->when($yearlevelId, fn ($query) => $query->where('yearlevel_id', $yearlevelId));

    //     return [
    //         'all' => Tab::make()
    //             ->label('All')
    //             ->badgeColor('info')
    //             ->badge($baseQuery->count()),

    //         'unpaid' => Tab::make('Not Fully Paid')
    //             ->badgeColor('danger')
    //             ->badge($baseQuery->clone()->whereNull('status')->count())
    //             ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('status')),

    //         'paid' => Tab::make('Fully Paid')
    //             ->badgeColor('success')
    //             ->badge($baseQuery->clone()->where('status', 'paid')->count())
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paid')),
    //     ];
    // }


    // protected function paginateTableQuery(Builder $query): Paginator
    // {
    //     return $query->simplePaginate(($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage());
    // }
}
