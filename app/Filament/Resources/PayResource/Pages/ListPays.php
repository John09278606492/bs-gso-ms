<?php

namespace App\Filament\Resources\PayResource\Pages;

use App\Filament\Exports\PayExporter;
use App\Filament\Resources\PayResource;
use App\Filament\Resources\PayResource\Widgets\PaysWidget;
use App\Models\Pay;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class ListPays extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = PayResource::class;

    protected ?string $maxContentWidth = 'full';

    protected function getTableQuery(): ?Builder
    {
        return static::getResource()::getEloquentQuery();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(),
            // ExportAction::make()
            //     ->exporter(PayExporter::class)
            //     ->color('success')
            //     ->formats([
            //         ExportFormat::Csv,
            //     ])
            //     ->columnMapping(false)
            //     ->icon('heroicon-m-arrow-down-on-square-stack')
            //     ->label('Export Payment Record')
            //     ->modalHeading('Export Student Payment Record')
            //     ->fileName(fn (Export $export): string => "student-payment-record-{$export->getKey()}.csv")
            Action::make('export-to-excel')
                ->color('success')
                ->icon('heroicon-m-printer')
                ->label('Export to EXCEL')
                // ->action(function () {
                //     Notification::make()
                //         ->title('EXCEL Export in Progress')
                //         ->body('Your student payment records export is being processed. Please wait for a moment.')
                //         ->info()
                //         ->color('info')
                //         ->send();
                // })
                ->url(function () {
                    // Retrieve the date filter from the table filters
                    $dateFilter = $this->tableFilters['created_at']['created_at'] ?? null;

                    // Validate if the date filter is empty
                    if (empty($dateFilter)) {
                        return null; // Do not show notifications immediately
                    }

                    // Ensure date range is correctly formatted (DD/MM/YYYY - DD/MM/YYYY)
                    if (strpos($dateFilter, ' - ') === false) {
                        return null; // Do not show notifications immediately
                    }

                    [$startDate, $endDate] = explode(' - ', $dateFilter);

                    try {
                        // Convert to YYYY-MM-DD format
                        $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($startDate))->format('Y-m-d');
                        $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($endDate))->format('Y-m-d');
                    } catch (\Exception $e) {
                        return null; // Do not show notifications immediately
                    }

                    // Fetch payments within the date range (full-day coverage)
                    $payments = Pay::whereBetween('created_at', [
                        $startDate . ' 00:00:00',
                        $endDate . ' 23:59:59'
                    ])->exists();

                    if (!$payments) {
                        return null; // Do not show notifications immediately
                    }

                    // Return the export URL if everything is valid
                    return route('EXPORT.PAYMENT.RECORDS', [
                        'date_from' => $startDate,
                        'date_to' => $endDate,
                    ]);
                })
                ->after(function () {
                    // Retrieve the date filter from the table filters
                    $dateFilter = $this->tableFilters['created_at']['created_at'] ?? null;

                    // Show validation messages **only after clicking the button**
                    if (empty($dateFilter)) {
                        Notification::make()
                            ->title('Date Range Not Specified')
                            ->body('Please specify the date range to filter the data.')
                            ->danger()
                            ->send();
                    } elseif (strpos($dateFilter, ' - ') === false) {
                        Notification::make()
                            ->title('Invalid Date Range Format')
                            ->body('Please specify the date range in the correct format (DD/MM/YYYY - DD/MM/YYYY).')
                            ->danger()
                            ->send();
                    } else {
                        [$startDate, $endDate] = explode(' - ', $dateFilter);

                        try {
                            // Convert to YYYY-MM-DD format
                            $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($startDate))->format('Y-m-d');
                            $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($endDate))->format('Y-m-d');
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Invalid Date Format')
                                ->body('Please ensure the date range is in the correct format (DD/MM/YYYY - DD/MM/YYYY).')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Fetch payments within the date range (full-day coverage)
                        $payments = Pay::whereBetween('created_at', [
                            $startDate . ' 00:00:00',
                            $endDate . ' 23:59:59'
                        ])->exists();

                        if (!$payments) {
                            Notification::make()
                                ->title('No Records Found')
                                ->body('No payment records found for the specified date range.')
                                ->danger()
                                ->send();
                        }
                    }
                }),
            // Action::make('export-to-pdf')
            //     ->color('danger')
            //     ->icon('heroicon-m-printer')
            //     ->label('Export to PDF')
            //     ->url(function () {
            //         // Retrieve the date filter from the table filters
            //         $dateFilter = $this->tableFilters['created_at']['created_at'] ?? null;

            //         // Validate if the date filter is empty
            //         if (empty($dateFilter)) {
            //             return null; // Do not show notifications immediately
            //         }

            //         // Ensure date range is correctly formatted (DD/MM/YYYY - DD/MM/YYYY)
            //         if (strpos($dateFilter, ' - ') === false) {
            //             return null; // Do not show notifications immediately
            //         }

            //         [$startDate, $endDate] = explode(' - ', $dateFilter);

            //         try {
            //             // Convert to YYYY-MM-DD format
            //             $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($startDate))->format('Y-m-d');
            //             $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($endDate))->format('Y-m-d');
            //         } catch (\Exception $e) {
            //             return null; // Do not show notifications immediately
            //         }

            //         // Check if there are payment records for the specified date range
            //         $payments = Pay::whereBetween('created_at', [
            //             $startDate . ' 00:00:00', // Ensure start of the day
            //             $endDate . ' 23:59:59'    // Ensure end of the day
            //         ])->exists();

            //         if (!$payments) {
            //             return null; // Do not show notifications immediately
            //         }

            //         // If everything is valid, return the route with the date range
            //         return route('EXPORT.PAYMENT.RECORDS.PDF', [
            //             'startDate' => $startDate,
            //             'endDate' => $endDate,
            //         ]);
            //     })
            //     ->after(function () {
            //         // Retrieve the date filter from the table filters
            //         $dateFilter = $this->tableFilters['created_at']['created_at'] ?? null;

            //         // Show validation messages **only after clicking the button**
            //         if (empty($dateFilter)) {
            //             Notification::make()
            //                 ->title('Date Range Not Specified')
            //                 ->body('Please specify the date range to filter the data.')
            //                 ->danger()
            //                 ->send();
            //         } elseif (strpos($dateFilter, ' - ') === false) {
            //             Notification::make()
            //                 ->title('Invalid Date Range Format')
            //                 ->body('Please specify the date range in the correct format (DD/MM/YYYY - DD/MM/YYYY).')
            //                 ->danger()
            //                 ->send();
            //         } else {
            //             [$startDate, $endDate] = explode(' - ', $dateFilter);

            //             try {
            //                 // Convert to YYYY-MM-DD format
            //                 $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($startDate))->format('Y-m-d');
            //                 $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($endDate))->format('Y-m-d');
            //             } catch (\Exception $e) {
            //                 Notification::make()
            //                     ->title('Invalid Date Format')
            //                     ->body('Please ensure the date range is in the correct format (DD/MM/YYYY - DD/MM/YYYY).')
            //                     ->danger()
            //                     ->send();
            //                 return;
            //             }

            //             // Check if there are payment records for the specified date range
            //             $payments = Pay::whereBetween('created_at', [
            //                 $startDate . ' 00:00:00',
            //                 $endDate . ' 23:59:59'
            //             ])->exists();

            //             if (!$payments) {
            //                 Notification::make()
            //                     ->title('No Records Found')
            //                     ->body('No payment records found for the specified date range.')
            //                     ->danger()
            //                     ->send();
            //             }
            //         }
            //     })
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            PaysWidget::class,
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return __('Payment Records');
    }
}
