<?php

namespace App\Filament\Exports;

use App\Models\Enrollment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use stdClass;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class EnrollmentExporter extends Exporter
{
    protected static ?string $model = Enrollment::class;

    public static function getColumns(): array
    {
        static $counter = 1;
        static $runningBalance = 0;

        return [
            ExportColumn::make('count')
                ->label('#')
                ->state(function (stdClass $row) use (&$counter) {
                    return (string) $counter++;
                }),
            ExportColumn::make('stud.studentidn')
                ->label('I.D Number'),
            ExportColumn::make('stud.full_name')
                ->label('Complete Name'),
            ExportColumn::make('college.college')
                ->label('College'),
            ExportColumn::make('program.program')
                ->label('Program'),
            ExportColumn::make('yearlevel.yearlevel')
                ->label('Year Level'),
            ExportColumn::make('schoolyear.schoolyear')
                ->label('School Year'),
            ExportColumn::make('balance')
                ->label('Remaining Balance'),
            ExportColumn::make('cumulative_balance')
                ->state(function (Enrollment $record) use (&$runningBalance) {
                    $runningBalance += $record->balance;
                    return number_format($runningBalance, 2);
                }),
            ExportColumn::make('status')
                ->label('Status'),
        ];
    }


    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'The student payment information export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
