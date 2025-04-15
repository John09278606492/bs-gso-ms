<?php

namespace App\Filament\Imports;

use App\Models\Stud;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;

class StudImporter extends Importer
{
    protected static ?string $model = Stud::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('studentidn')
                ->label('Student IDN')
                ->exampleHeader('Student IDN')
                ->requiredMapping()
                ->rules(['required', 'numeric']),
            ImportColumn::make('firstname')
                ->label('First Name')
                ->exampleHeader('First Name')
                ->requiredMapping()
                ->fillRecordUsing(function (Stud $record, string $state): void {
                    $record->firstname = collect(explode(' ', strtolower($state)))
                        ->map(fn($word) => ucfirst($word))
                        ->join(' ');
                })
                ->rules(['required', 'regex:/^[^0-9]*$/']),
            ImportColumn::make('middlename')
                ->ignoreBlankState()
                ->label('Middle Name')
                ->exampleHeader('Middle Name')
                ->rules(['nullable', 'max:255', 'regex:/^[^0-9]*$/'])
                ->fillRecordUsing(function (Stud $record, string $state): void {
                    $record->middlename = collect(explode(' ', strtolower($state)))
                        ->map(fn($word) => ucfirst($word))
                        ->join(' ');
                }),
            ImportColumn::make('lastname')
                ->label('Last Name')
                ->exampleHeader('Last Name')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'regex:/^[^0-9]*$/'])
                ->fillRecordUsing(function (Stud $record, string $state): void {
                    $record->lastname = collect(explode(' ', strtolower($state)))
                        ->map(fn($word) => ucfirst($word))
                        ->join(' ');
                }),
            ImportColumn::make('status')
                ->ignoreBlankState()
                ->label('Status')
                ->exampleHeader('Status')
                ->rules(['nullable', 'max:255', 'in:active,inactive,graduated', 'regex:/^[^0-9]*$/']),
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'studentidn.required' => 'The Student IDN field is required.',
            'studentidn.numeric' => 'Student IDN must be a numeric value.',
            'studentidn.max' => 'Student IDN must not greater than 255 digits.',
            'firstname.required' => 'The First Name field is required.',
            'firstname.regex' => 'The First Name must contain only letters, dashes and spaces.',
            'middlename.regex' => 'The Middle Name must contain only letters, dashes and spaces.',
            'lastname.required' => 'The Last Name field is required.',
            'lastname.regex' => 'The Last Name must contain only letters, dashes and spaces.',
            'status.regex' => 'The Status must contain only letters.',
            'status.in' => 'The Status must be one of: active, inactive, or graduated.',
        ];
    }

    public function resolveRecord(): ?Stud
    {

        return Stud::firstOrNew([
            'studentidn' => $this->data['studentidn'],
        ]);
        // Find or create the student record
        // $students = Stud::firstOrNew([
        //     'studentidn' => $this->data['studentidn'],
        // ]);

        // // Update student details
        // $students->firstname = $this->data['firstname'] ?? $students->firstname;
        // $students->middlename = $this->data['middlename'] ?? $students->middlename;
        // $students->lastname = $this->data['lastname'] ?? $students->lastname;

        // // Save the student record
        // $students->save();

        // return $students;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $recipient = auth()->user();

        // Base message
        $body = 'Your student import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        // Check for failed rows
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $downloadUrl = url("/filament/imports/{$import->id}/failed-rows/download");

            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';

            // Styled download link with independent hover underline effect
            $downloadLink = '<a href="' . $downloadUrl . '" target="_blank" class="text-sm font-semibold text-danger-600 dark:text-danger-400 hover:underline">
                            Download information about the failed row
                         </a>';

            Notification::make()
                ->title('Import completed')
                ->body(new HtmlString($body . '<br>' . $downloadLink))
                ->danger()
                ->sendToDatabase($recipient);
        }

        Notification::make()
            ->title('Import completed')
            ->body($body)
            ->success()
            ->sendToDatabase($recipient);

        return $body;
    }

    // public static function getCompletedNotificationBody(Import $import): string
    // {
    //     $body = 'Your student import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

    //     if ($failedRowsCount = $import->getFailedRowsCount()) {
    //         $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
    //     }

    //     return $body;
    // }
}
