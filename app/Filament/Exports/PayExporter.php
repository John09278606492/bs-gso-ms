<?php

namespace App\Filament\Exports;

use App\Models\Pay;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use stdClass;

class PayExporter extends Exporter
{
    protected static ?string $model = Pay::class;

    public static function getColumns(): array
    {
        static $counter = 1;
        return [
            ExportColumn::make('count')
                ->label('#')
                ->state(function (stdClass $row) use (&$counter) {
                    return (string) $counter++;
                }),
            ExportColumn::make('enrollment.stud.studentidn')
                ->label('I.D Number'),
            ExportColumn::make('enrollment.stud.lastname')
                ->label('Last Name'),
            ExportColumn::make('enrollment.stud.firstname')
                ->label('First Name'),
            ExportColumn::make('enrollment.stud.middlename')
                ->label('Middle Name'),
            ExportColumn::make('enrollment.college.college')
                ->label('College'),
            ExportColumn::make('enrollment.program.program')
                ->label('Program'),
            ExportColumn::make('enrollment.yearlevel.yearlevel')
                ->label('Year Level'),
            ExportColumn::make('enrollment.schoolyear.schoolyear')
                ->label('School Year'),
            ExportColumn::make('amount')
                ->label('Amount'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('created_at')
                ->label('Date/Time Paid'),
            ExportColumn::make('updated_at')
                ->label('Date/Time Updated'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'The student payment record export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
