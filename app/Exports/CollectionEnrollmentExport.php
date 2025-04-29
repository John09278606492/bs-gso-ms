<?php

namespace App\Exports;

use App\Models\CollectionEnrollment;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Events\AfterSheet;

class CollectionEnrollmentExport implements FromQuery, WithHeadings, WithMapping, WithEvents, ShouldAutoSize, WithDrawings, WithCustomStartCell
{
    use Exportable;

    private $rowNumber = 0;
    protected $startDate;
    protected $endDate;
    private $dateFrom;
    private $dateTo;
    private $collegeId;
    private $programId;
    private $yearlevelId;
    private $totalPayments = 0;
    private $collectionIds;

    public function __construct(?string $dateFrom = null, ?string $dateTo = null, array $collectionIds = [])
    // public function __construct(?string $dateFrom = null, ?string $dateTo = null, ?int $collegeId = null, ?int $programId = null, ?int $yearlevelId = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->collectionIds = $collectionIds;
        // $this->collegeId = $collegeId;
        // $this->programId = $programId;
        // $this->yearlevelId = $yearlevelId;
    }


    public function query()
    {
        $query = CollectionEnrollment::query()
            ->with(['enrollment.college', 'enrollment.program', 'enrollment.yearlevel', 'enrollment.schoolyear', 'enrollment.stud', 'collection'])
            ->whereNotNull('collection_status')
            ->whereNotNull('updated_at');

        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('updated_at', [$this->dateFrom, $this->dateTo]);
        }

        // Filter by multiple collection IDs if provided
        if (!empty($this->collectionIds)) {
            $query->whereIn('collection_id', $this->collectionIds);
        }

        // Order by updated_at ascending
        $query->orderBy('updated_at', 'asc');

        return $query;
    }


    public function headings(): array
    {
        return [
            'No.',
            'Student Name',
            'College',
            'Program',
            'Year Level',
            'School Year',
            'Category Name',
            'Collection Status',
            'Date/Time Paid',
            'Amount',
        ];
    }

    public function map($row): array
    {
        $payments = $row->collection?->amount ?? 0;

        $this->totalPayments += $payments;

        number_format($this->totalPayments, 2);

        return [
            ++$this->rowNumber,
            $row->enrollment?->stud?->fullname ?? 'N/A',
            $row->enrollment?->college?->college ?? 'N/A',
            $row->enrollment?->program?->program ?? 'N/A',
            $row->enrollment?->yearlevel?->yearlevel ?? 'N/A',
            $row->enrollment?->schoolyear?->schoolyear ?? 'N/A',
            $row->collection
                ? 'Semester ' . ($row->collection->semester->semester ?? '-') . ': ' . ($row->collection->description ?? '-')
                : 'N/A',
            $row->collection_status ?? 'N/A',
            $row->updated_at ? $row->updated_at->format('M d, Y - h:i a') : 'N/A',
            number_format($payments, 2), // <-- formatted with 2 decimal points
        ];
    }

    public function startCell(): string
    {
        return 'A10'; // Start data at row 10
    }

    public function drawings()
    {
        $drawing1 = new Drawing();
        $drawing1->setPath(public_path('/images/bisu logo2.png'));
        $drawing1->setHeight(96);
        $drawing1->setOffsetX(80);
        $drawing1->setCoordinates('B2');

        $drawing2 = new Drawing();
        $drawing2->setPath(public_path('/images/bagong_pilipinas.png'));
        $drawing2->setHeight(100);
        $drawing2->setOffsetX(80);
        $drawing2->setCoordinates('G2');

        $drawing3 = new Drawing();
        $drawing3->setPath(public_path('/images/tuv logo.png'));
        $drawing3->setHeight(96);
        $drawing3->setOffsetX(80);
        $drawing3->setCoordinates('H2');

        return [$drawing1, $drawing2, $drawing3];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Header Information
                $sheet->setCellValue('B2', 'Republic of the Philippines');
                $sheet->setCellValue('B3', 'BOHOL ISLAND STATE UNIVERSITY');
                $sheet->setCellValue('B4', 'San Isidro, Calape, Bohol');
                $sheet->setCellValue('B5', 'Graduating Students Organization');
                $sheet->setCellValue('B6', 'Balance | Integrity | Stewardship | Uprightness');

                $sheet->getStyle('B2:B6')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)->setIndent(20);

                // Merge cells
                $sheet->mergeCells('B2:E2');
                $sheet->mergeCells('B3:E3');
                $sheet->mergeCells('B4:E4');
                $sheet->mergeCells('B5:E5');
                $sheet->mergeCells('B6:E6');
                $sheet->mergeCells('G2:H2');
                $sheet->mergeCells('G3:H3');
                $sheet->mergeCells('G4:H4');
                $sheet->mergeCells('G5:H5');
                $sheet->mergeCells('G6:H6');

                // Left align header
                for ($row = 2; $row <= 6; $row++) {
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                }

                $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('B6')->getFont()->setBold(true)->setSize(12);

                // Row heights
                for ($row = 2; $row <= 7; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(14);
                }

                // Report title
                $titleRow = 8;
                $sheet->mergeCells("A{$titleRow}:J{$titleRow}");
                $sheet->setCellValue("A{$titleRow}", "Student Collection Records");
                $sheet->getRowDimension($titleRow)->setRowHeight(30);
                $sheet->getStyle("A{$titleRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Date range
                $sheet->setCellValue(
                    'B9',
                    'Date Range: ' .
                        ($this->dateFrom ? date('M d, Y', strtotime($this->dateFrom)) : 'N/A') . ' - ' .
                        ($this->dateTo ? date('M d, Y', strtotime($this->dateTo)) : 'N/A')
                );

                $sheet->getStyle('B9')->getFont()->setBold(true);
                $sheet->getRowDimension(9)->setRowHeight(30);
                $sheet->getStyle("B9")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                ]);

                // Style for headings
                $startingRow = 10;
                $lastRow = $startingRow + $this->rowNumber;
                $summaryRow = $lastRow + 1;

                $sheet->getStyle("A{$startingRow}:J{$startingRow}")->getFont()->setBold(true);

                // Borders for all data
                $cellRange = "A{$startingRow}:J{$lastRow}";
                $sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'shrinkToFit' => true,
                        'wrapText' => true,
                    ],
                ]);

                // Total Amount Collected
                $sheet->setCellValue("H{$summaryRow}", 'Total Amount Collected:');
                $sheet->setCellValue("J{$summaryRow}", number_format($this->totalPayments, 2));

                $sheet->getStyle("H{$summaryRow}:J{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
            },
        ];
    }
}
