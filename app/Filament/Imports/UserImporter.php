<?php

namespace App\Filament\Imports;

use App\Models\Stud;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    private $students;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('canId')
                ->label('Student IDN')
                ->exampleHeader('Student IDN')
                ->requiredMapping()
                ->rules(['required', 'numeric']),
            ImportColumn::make('firstname')
                ->label('First Name')
                ->exampleHeader('First Name')
                ->requiredMapping()
                ->fillRecordUsing(function (User $record, string $state): void {
                    $record->firstname = collect(explode(' ', strtolower($state)))
                        ->map(fn($word) => ucfirst($word))
                        ->join(' ');
                })
                ->rules(['required', 'max:255', 'regex:/^[^0-9]*$/']),
            ImportColumn::make('middlename')
                ->ignoreBlankState()
                ->label('Middle Name')
                ->exampleHeader('Middle Name')
                ->fillRecordUsing(function (User $record, string $state): void {
                    $record->middlename = collect(explode(' ', strtolower($state)))
                        ->map(fn($word) => ucfirst($word))
                        ->join(' ');
                })
                ->rules(['nullable', 'max:255', 'regex:/^[^0-9]*$/']),
            ImportColumn::make('lastname')
                ->label('Last Name')
                ->exampleHeader('Last Name')
                ->requiredMapping()
                ->fillRecordUsing(function (User $record, string $state): void {
                    $record->lastname = collect(explode(' ', strtolower($state)))
                        ->map(fn($word) => ucfirst($word))
                        ->join(' ');
                })
                ->rules(['required', 'max:255', 'regex:/^[^0-9]*$/']),
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'canId.required' => 'The Student IDN field is required.',
            'canId.numeric' => 'Student IDN must be a numeric value.',
            'firstname.required' => 'The First Name field is required.',
            'firstname.regex' => 'The First Name must contain only letters, dashes and spaces.',
            'middlename.regex' => 'The Middle Name must contain only letters, dashes and spaces.',
            'lastname.required' => 'The Last Name field is required.',
            'lastname.regex' => 'The Last Name must contain only letters, dashes and spaces.',
        ];
    }

    private function loadLookups(): void
    {
        if (! $this->students) {
            $this->students = Stud::select('id', 'studentidn')->get();
        }
    }

    public function resolveRecord(): ?User
    {
        $this->loadLookups();

        // Find Student
        $student = $this->students->firstWhere('studentidn', $this->data['canId']);
        if (!$student) {
            throw new RowImportFailedException('No student idn found');
        }

        // Sanitize and format names
        $firstName = $this->sanitizeName($this->data['firstname'] ?? '');
        $lastName = $this->sanitizeName($this->data['lastname'] ?? '');

        // Create email1 using the convention firstname.lastname@bisu.edu.ph
        $email1 = strtolower($firstName . '.' . $lastName . '@bisu.edu.ph');

        return User::updateOrCreate(
            ['canID' => $student->studentidn], // Condition to find existing record
            [
                'name' => ucwords($firstName . ' ' . $lastName),
                'email' => $email1,
                'email1' => 'gso' . '@' . $student->studentidn,
                'password' => Hash::make($student->studentidn),
                'role' => 'guest',
            ]
        );
    }

    /**
     * Sanitize a name by:
     * - Removing special characters and replacing them with the closest letter.
     * - Removing spaces completely (e.g., "Jean Paul" → "jeanpaul").
     * - Converting to lowercase.
     */
    private function sanitizeName(string $name): string
    {
        // Convert to ASCII (e.g., "ñ" → "n", "é" → "e")
        $name = Str::ascii($name);

        // Remove non-alphabetic characters (keeps only a-z and A-Z)
        $name = preg_replace('/[^a-zA-Z]/', '', $name);

        // Convert to lowercase
        return strtolower($name);
    }

    // public function resolveRecord(): ?User
    // {
    //     $this->loadLookups();
    //     // Find Student
    //     $student = $this->students->firstWhere('studentidn', $this->data['canId']);
    //     if (!$student) {
    //         throw new RowImportFailedException('No student idn found');
    //     }

    //     $firstName = ucwords(strtolower($this->data['firstname'] ?? ''));
    //     $middleName = ucwords(strtolower($this->data['middle'] ?? ''));
    //     $lastName = ucwords(strtolower($this->data['lastname'] ?? ''));

    //     $fullName = trim("$firstName $middleName $lastName");

    //     return User::firstOrNew(
    //         [
    //             'canID' => $student->studentidn,
    //         ],
    //         [
    //             'name' => $fullName,
    //             'email' => 'ptgea' . '@' . $student->studentidn,
    //             'password' => Hash::make($student->studentidn),
    //             'role' => 'guest',
    //         ]
    //     );
    // }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $recipient = auth()->user();
        $body = 'Your student account import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $downloadUrl = url("/filament/imports/{$import->id}/failed-rows/download");

            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';

            // Styled download link with independent hover underline effect
            $downloadLink = '<a href="' . $downloadUrl . '" target="_blank" class="text-sm font-semibold no-underline text-danger-600 dark:text-danger-400 hover:underline">
                    Download information about the failed row
                 </a>';

            Notification::make()
                ->title('Import completed')
                ->body(new HtmlString($body . '<br>' . $downloadLink))
                ->danger()
                ->sendToDatabase($recipient);
        }

        // Notification::make()
        //     ->title('Import completed')
        //     ->body($body)
        //     ->success()
        //     ->sendToDatabase($recipient);

        return $body;
    }
}
