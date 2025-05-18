<?php

namespace App\Exports;

use App\Models\Enrollment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class StudentpaymentExport implements WithMapping, WithHeadings, ShouldAutoSize, WithEvents, FromQuery, WithDrawings, WithCustomStartCell
{
    use Exportable;

    private $rowNumber = 0;
    private $totalBalance = 0;
    private $totalPayments = 0;
    private $totalRefunds = 0;
    private $final_balance = 0;
    private $year;
    private $collegeId;
    private $programId;
    private $yearlevelId;
    private $status;
    private $refund;

    public function __construct(?int $year = null, ?int $collegeId = null, ?int $programId = null, ?int $yearlevelId = null, ?string $status = null)
    {
        $this->year = $year;
        $this->collegeId = $collegeId;
        $this->programId = $programId;
        $this->yearlevelId = $yearlevelId;
        $this->status = $status;
    }

    public function query()
    {
        $query = Enrollment::query();

        if ($this->year) {
            $query->where('schoolyear_id', $this->year);
        }
        if ($this->collegeId) {
            $query->where('college_id', $this->collegeId);
        }
        if ($this->programId) {
            $query->where('program_id', $this->programId);
        }
        if ($this->yearlevelId) {
            $query->where('yearlevel_id', $this->yearlevelId);
        }
        if ($this->status === 'paid') {
            $query->where('status', 'paid');
        } elseif ($this->status === 'not_paid') {
            $query->whereNull('status');
        } elseif ($this->status === '' || $this->status === null) {
            $query->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 'paid');
            });
        }

        return $query;
    }

    public function startCell(): string
    {
        return 'A10';
    }

    public function map($students): array
    {
        $refundable_amount = is_numeric($students->refundable_amount) ? (float)$students->refundable_amount : 0;
        $balance = is_numeric($students->balance) ? (float)$students->balance : 0;
        $payments = is_numeric($students->payments) ? (float)$students->payments : 0;

        $this->refund = number_format($balance, 2);

        if ($balance < 0) {
            $this->refund = 'Refundable Amount: ' . number_format(abs($balance), 2);
        }

        $this->totalBalance += $balance;
        $this->totalPayments += $payments;
        $this->totalRefunds += $this->refund;

        return [
            ++$this->rowNumber,
            $students->stud->studentidn,
            $students->stud->lastname . ', ' . $students->stud->firstname . ', ' . $students->stud->middlename,
            $students->college->college,
            $students->program->program,
            $students->yearlevel->yearlevel,
            $students->schoolyear->schoolyear,
            number_format($payments, 2),
            $this->refund
        ];
    }

    public function drawings()
    {
        $drawing1 = new Drawing();
        $drawing1->setPath(public_path('/images/bisu logo2.png'));
        $drawing1->setHeight(96);
        $drawing1->setCoordinates('C2');

        $drawing2 = new Drawing();
        $drawing2->setPath(public_path('/images/bagong_pilipinas.png'));
        $drawing2->setHeight(100);
        $drawing2->setOffsetX(150);
        $drawing2->setCoordinates('G2');

        $drawing3 = new Drawing();
        $drawing3->setPath(public_path('/images/tuv logo.png'));
        $drawing3->setHeight(96);
        $drawing3->setOffsetX(70);
        $drawing3->setCoordinates('H2');

        return [$drawing1, $drawing2, $drawing3];
    }

    public function headings(): array
    {
        return [
            '#',
            'Student IDN',
            'Complete Name',
            'College',
            'Program',
            'Year Level',
            'School Year',
            'Total Amount Paid',
            'Total Remaining Balance',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->setCellValue('C2', 'Republic of the Philippines');
                $sheet->setCellValue('C3', 'BOHOL ISLAND STATE UNIVERSITY');
                $sheet->setCellValue('C4', 'San Isidro, Calape, Bohol');
                $sheet->setCellValue('C5', 'Graduating Students Organization');
                $sheet->setCellValue('C6', 'Balance | Integrity | Stewardship | Uprightness');

                $sheet->getStyle('C2:C6')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)->setIndent(12);

                $sheet->mergeCells('C2:E2');
                $sheet->mergeCells('C3:E3');
                $sheet->mergeCells('C4:E4');
                $sheet->mergeCells('C5:E5');
                $sheet->mergeCells('C6:E6');

                for ($row = 2; $row <= 6; $row++) {
                    $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                }

                $sheet->getStyle('C3')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('C6')->getFont()->setBold(true)->setSize(12);

                for ($row = 2; $row <= 7; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(14);
                }

                $titleRow = 8;
                $sheet->mergeCells("A{$titleRow}:I{$titleRow}");
                $sheet->setCellValue("A{$titleRow}", "Student Payment Information");
                $sheet->getRowDimension($titleRow)->setRowHeight(30);

                $sheet->getStyle("A{$titleRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $startingRow = 10;
                $lastRow = $startingRow + $this->rowNumber;
                $summaryRow = $lastRow + 1;
                $totalRow = $summaryRow + 1;

                $sheet->getStyle("A{$startingRow}:I{$startingRow}")->getFont()->setBold(true);

                $sheet->getStyle("A{$startingRow}:I{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    'alignment' => ['shrinkToFit' => true, 'wrapText' => true],
                ]);

                $sheet->setCellValue("G{$summaryRow}", 'Grand Total Amount Collected:');
                $sheet->setCellValue("H{$summaryRow}", number_format($this->totalPayments, 2));

                $sheet->setCellValue("G{$totalRow}", 'Grand Total Remaining Balance:');
                $sheet->setCellValue("I{$totalRow}", number_format(max(0, $this->totalBalance), 2));

                $grandRefundableRow = $totalRow + 1;
                $sheet->setCellValue("G{$grandRefundableRow}", 'Grand Total Refundable Amount:');
                $sheet->mergeCells("H{$grandRefundableRow}:I{$grandRefundableRow}");
                $sheet->setCellValue("H{$grandRefundableRow}", number_format($this->totalRefunds, 2));

                // Apply bold styling to both label and value
                $sheet->getStyle("G{$grandRefundableRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, // Align numbers to the left
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                ]);

                $sheet->getStyle("H{$grandRefundableRow}:I{$grandRefundableRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, // Align numbers to the left
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                ]);

                $overallRow = $grandRefundableRow + 1;
                $sheet->setCellValue("G{$overallRow}", 'Overall Total Expected Collection:');
                $sheet->mergeCells("H{$overallRow}:I{$overallRow}");
                $sheet->setCellValue("H{$overallRow}", number_format($this->totalPayments + $this->totalBalance, 2));

                // Apply bold styling and left alignment for summary labels (column G)
                $sheet->getStyle("G{$summaryRow}:G{$overallRow}")->applyFromArray([
                    'font' => ['bold' => true], // Keep the text bold
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, // Align labels to the left
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                ]);

                // Apply bold styling and right alignment for summary values (columns H & I)
                $sheet->getStyle("H{$summaryRow}:I{$overallRow}")->applyFromArray([
                    'font' => ['bold' => true], // Keep the text bold
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, // Align numbers to the right
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                ]);
            },
        ];
    }
}
