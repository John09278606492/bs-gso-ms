<?php

namespace App\Filament\Resources\CollectionEnrollmentResource\Pages;

use App\Exports\CollectionEnrollmentExport;
use App\Filament\Resources\CollectionEnrollmentResource;
use App\Filament\Resources\CollectionEnrollmentResource\Widgets\CollectionRecordsWidget;
use App\Models\CollectionEnrollment;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;

class ListCollectionEnrollments extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CollectionEnrollmentResource::class;

    protected function getTableQuery(): ?Builder
    {
        return static::getResource()::getEloquentQuery();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(),
            Action::make('export-to-excel')
                ->color('success')
                ->icon('heroicon-m-printer')
                ->label('Export to EXCEL')
                ->after(function () {
                    $filters = $this->tableFilters['updated_at']['updated_at'] ?? null;
                    $collection_id = $this->tableFilters['filters']['collection_id'] ?? null;
                    // $college_id = $this->tableFilters['filters']['college_id'] ?? null;
                    // $program_id = $this->tableFilters['filters']['program_id'] ?? null;
                    // $yearlevel_id = $this->tableFilters['filters']['yearlevel_id'] ?? null;

                    // dd($college_id, $program_id, $yearlevel_id);

                    if (empty($filters)) {
                        Notification::make()
                            ->title('Date Range is required')
                            ->body('Please select a date range before exporting.')
                            ->danger()
                            ->send();

                        return;
                    }

                    // Parse the dates
                    [$startDate, $endDate] = explode(' - ', $filters);

                    // dd($startDate . ' - ' . $endDate);

                    try {
                        // Convert to YYYY-MM-DD format
                        $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($startDate))->format('Y-m-d');
                        $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($endDate))->format('Y-m-d');
                    } catch (\Exception $e) {
                        return null; // Do not show notifications immediately
                    }

                    // Check if there are records
                    $collectionIds = (array) $collection_id;  // Ensure it's treated as an array

                    $query = CollectionEnrollment::whereNotNull('collection_status')
                        ->whereNotNull('updated_at')
                        ->whereBetween('updated_at', [
                            $startDate . ' 00:00:00',
                            $endDate . ' 23:59:59'
                        ]);

                    // If collectionIds is not empty, filter by collection_id
                    if (!empty($collectionIds)) {
                        $query->whereIn('collection_id', $collectionIds);  // Use whereIn for multiple collection_id
                    }

                    $exists = $query->exists();

                    if (!$exists) {
                        Notification::make()
                            ->title('No Records Found')
                            ->body('There are no records for the selected date range.')
                            ->warning()
                            ->send();

                        return;
                    }

                    // Notification::make()
                    //     ->title('Export Started')
                    //     ->body('Your export is being processed and will be available shortly.')
                    //     ->success()
                    //     ->send();

                    return Excel::download(new CollectionEnrollmentExport($startDate . ' 00:00:00', $endDate . ' 23:59:59', $collectionIds), 'Student-Collection-Records-Export-' . now()->format('Y-m-d_H-i') . '.xlsx');
                })
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            CollectionRecordsWidget::class,
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return __('Collection Records');
    }
}
