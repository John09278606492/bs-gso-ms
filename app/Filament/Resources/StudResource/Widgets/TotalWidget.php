<?php

namespace App\Filament\Resources\StudResource\Widgets;

use App\Models\Enrollment;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalWidget extends BaseWidget
{
    // protected function getStats(): array
    // {
    //     return [
    //         Stat::make('Total', Enrollment::summarizeAmounts())
    //             ->description('Expected Collection Amount')
    //             ->descriptionIcon('heroicon-m-banknotes', IconPosition::Before)
    //             ->color('success'),
    //     ];
    // }
}
