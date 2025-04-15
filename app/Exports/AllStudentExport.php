<?php

namespace App\Exports;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

class AllStudentExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithEvents
{
    private $rowNumber = 0;
    private $totalBalance = 0;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Fetch all enrollments
        $enrollments = Enrollment::all();

        // Calculate total balance
        $this->totalBalance = $enrollments->sum('balance');

        return $enrollments;
    }

    /**
     * @param Enrollment $students
     */
    public function map($students): array
    {
        return [
            ++$this->rowNumber,
            $students->stud->lastname . ', ' . $students->stud->firstname . ', ' . $students->stud->middlename,
            $students->college->college,
            $students->program->program,
            $students->yearlevel->yearlevel,
            $students->schoolyear->schoolyear,
            number_format($students->balance, 2) // Format balance with 2 decimal places
        ];
    }

    /**
     * Set column headings
     */
    public function headings(): array
    {
        return [
            '#',
            'Complete Name',
            'College',
            'Program',
            'Year Level',
            'School Year',
            'Remaining Balance',
        ];
    }

    /**
     * Add total balance at the bottom using AfterSheet event
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastRow = $this->rowNumber + 2; // +2 to account for header row and extra space
                $event->sheet->setCellValue('F' . $lastRow, 'Total Remaining Balance:');
                $event->sheet->setCellValue('G' . $lastRow, number_format($this->totalBalance, 2)); // Display total
                $event->sheet->getStyle('F' . $lastRow . ':G' . $lastRow)->getFont()->setBold(true); // Make text bold
            },
        ];
    }
}
