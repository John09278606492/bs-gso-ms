<?php

namespace App\Filament\Resources\EnrollmentResource\Widgets;

use App\Filament\Resources\EnrollmentResource\Pages\EditEnrollment;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalPayWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return EditEnrollment::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total', 0)
                ->description('Collected Amount')
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::Before)
                ->color('success'),
        ];
    }
}
