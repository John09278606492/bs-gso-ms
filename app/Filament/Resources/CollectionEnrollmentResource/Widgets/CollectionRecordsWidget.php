<?php

namespace App\Filament\Resources\CollectionEnrollmentResource\Widgets;

use App\Filament\Resources\CollectionEnrollmentResource\Pages\ListCollectionEnrollments;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CollectionRecordsWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListCollectionEnrollments::class;
    }

    private function calculateTotalPaid(): string
    {
        $paidTotal = $this->getPageTableQuery()
            ->with('collection') // Eager load the 'collection' relation
            ->where('collection_status', 'paid')
            ->get() // Fetch the records
            ->sum(function ($record) {
                return $record->collection?->amount ?? 0; // Sum the amount from the related collection
            });

        return '₱' . number_format($paidTotal, 2, '.', ',');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('', $this->calculateTotalPaid())
                ->description('Total Amount Paid)')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::After)
                ->color('success'),
        ];
    }
}
