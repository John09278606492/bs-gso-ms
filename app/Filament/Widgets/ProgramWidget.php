<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use App\Models\Program;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;

class ProgramWidget extends ChartWidget
{
    protected static ?string $heading = 'No. of students per Program';

    use InteractsWithPageFilters;

    // protected static bool $isLazy = false;

    protected static ?string $maxHeight = '275px';

    protected function getData(): array
    {
        // Get the school year filter value
        $schoolyearId = $this->filters['schoolyear_id'] ?? null;

        // Convert empty string or 'All' to null
        if ($schoolyearId === '' || $schoolyearId === 'All') {
            $schoolyearId = null;
        }

        // Fetch all programs
        $allPrograms = Program::all();

        // Initialize labels and data arrays
        $labels = [];
        $data = [];

        // Populate labels and student count data
        foreach ($allPrograms as $program) {
            // Use the program's name as the label
            $labels[] = $program->program;

            // Fetch the student count based on school year filter
            $studentCount = Enrollment::query()
                ->when($schoolyearId, function ($query) use ($schoolyearId) {
                    return $query->where('schoolyear_id', $schoolyearId); // Filter by school year if provided
                })
                ->where('program_id', $program->id)
                ->count(); // Count students in this program for the selected school year

            // Add the count to the dataset
            $data[] = $studentCount;
        }

        Log::debug('Y-Axis Data:', $data); // Log the student count data

        return [
            'datasets' => [
                [
                    'label' => 'No. of students',
                    'data' => $data, // Data of student counts per program
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                    ],
                    'hoverBackgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                    ],
                ],
            ],
            'labels' => $labels, // All program names as labels
            'options' => [
                'cutoutPercentage' => 50,
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Change to 'pie', 'doughnut', etc., for other chart types
    }
}
