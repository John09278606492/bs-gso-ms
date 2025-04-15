<?php

namespace App\Filament\Imports;

use App\Models\Collection;
use App\Models\College;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Schoolyear;
use App\Models\Semester;
use App\Models\Stud;
use App\Models\Yearlevel;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Actions\Imports\Exceptions;
use Filament\Forms\Components\Select;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;


class EnrollmentImporter extends Importer
{
    protected static ?string $model = Enrollment::class;

    private $students;

    private $colleges;

    private $programs;

    private $yearlevels;

    private $schoolyears;

    private $semesters;

    private $collections;

    public static function getColumns(): array
    {
        return [
            // ImportColumn::make('firstname')
            //     ->label('First Name')
            //     ->exampleHeader('First Name')
            //     ->requiredMapping()
            //     ->rules(['required']),
            ImportColumn::make('stud')
                ->label('Student IDN')
                ->exampleHeader('Student IDN')
                ->requiredMapping()
                ->relationship(resolveUsing: function (string $state): ?Stud {
                    return Stud::query()
                        ->where('studentidn', $state)
                        ->first();
                })
                ->rules(['required']),
            ImportColumn::make('college')
                ->label('College')
                ->exampleHeader('College')
                ->requiredMapping()
                ->relationship(resolveUsing: function (string $state): ?College {
                    return College::query()
                        ->where('college', $state)
                        ->first();
                })
                ->rules(['required', 'regex:/^[^0-9]*$/']),
            ImportColumn::make('program')
                ->label('Program')
                ->exampleHeader('Program')
                ->requiredMapping()
                ->relationship(resolveUsing: function (string $state): ?Program {
                    return Program::query()
                        ->where('program', $state)
                        ->first();
                })
                ->rules(['required', 'regex:/^[^0-9]*$/']),
            ImportColumn::make('yearlevel')
                ->label('Year Level')
                ->exampleHeader('Year Level')
                ->requiredMapping()
                ->relationship(resolveUsing: function (string $state, array $data): ?Yearlevel {
                    $program = Program::query()
                        ->where('program', $data['program'])
                        ->first();
                    if ($program) {
                        return Yearlevel::query()
                            ->where('yearlevel', $state)
                            ->where('program_id', $program->id)
                            ->first();
                    }

                    return null;
                })
                ->rules(['required', 'numeric']),
            // ImportColumn::make('schoolyear')
            //     ->label('School Year')
            //     ->exampleHeader('School Year')
            //     ->requiredMapping()
            //     ->relationship(resolveUsing: function (string $state): ?Schoolyear {
            //         return Schoolyear::query()
            //             ->where('schoolyear', $state)
            //             ->first();
            //     })
            //     ->rules([
            //         'required',
            //         'regex:/^\d{4} - \d{4}$/',
            //         function ($attribute, $value, $fail) {
            //             // Extract years
            //             [$firstYear, $secondYear] = explode(' - ', $value);

            //             // Convert to integers
            //             $firstYear = (int) $firstYear;
            //             $secondYear = (int) $secondYear;

            //             // Validate second year is greater than or equal to first year
            //             if ($secondYear < $firstYear) {
            //                 $fail("The second year ({$secondYear}) must be greater than or equal to the first year ({$firstYear}).");
            //             }
            //         },
            //     ]),
            ImportColumn::make('status')
                ->label('Status')
                ->exampleHeader('Status')
                ->ignoreBlankState()
                ->rules(['max:255']),
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'college.regex' => 'The College field must contain only letters, dashes and spaces.',
            'program.regex' => 'The Program field must contain only letters, dashes and spaces.',
            'yearlevel.numeric' => 'Yearlevel must be a numeric value.',
        ];
    }


    private function loadLookups(): void
    {
        if (! $this->students) {
            $this->students = Stud::select('id', 'studentidn')->get();
            $this->colleges = College::select('id', 'college')->get();
            $this->programs = Program::select('id', 'college_id', 'program')->get();
            $this->yearlevels = Yearlevel::select('id', 'program_id', 'yearlevel')->get();
            $this->schoolyears = Schoolyear::select('id', 'schoolyear')->get();
            $this->semesters = Semester::select('id', 'schoolyear_id', 'semester')->get();
            $this->collections = Collection::select('id', 'semester_id', 'amount', 'description')->get();
        }
    }

    // public function resolveRecord(): ?Enrollment
    // {
    //     $this->loadLookups();

    //     // Find or create College
    //     $college = $this->colleges->firstWhere(function ($college) {
    //         return strtolower($college->college) === strtolower($this->data['college']);
    //     });
    //     if (!$college) {
    //         $college = College::create(['college' => $this->data['college']]);
    //         $this->colleges->push($college);
    //     }

    //     // Find or create Program
    //     $program = $this->programs
    //         ->where('college_id', $college->id)
    //         ->firstWhere(function ($program) {
    //             return strtolower($program->program) === strtolower($this->data['program']);
    //         });
    //     if (!$program) {
    //         $program = Program::create(['college_id' => $college->id, 'program' => $this->data['program']]);
    //         $this->programs->push($program);
    //     }

    //     // Find or create Yearlevel
    //     $yearlevel = $this->yearlevels
    //         ->where('program_id', $program->id)
    //         ->firstWhere('yearlevel', $this->data['yearlevel']);
    //     if (!$yearlevel) {
    //         $yearlevel = Yearlevel::create(['program_id' => $program->id, 'yearlevel' => $this->data['yearlevel']]);
    //         $this->yearlevels->push($yearlevel);
    //     }

    //     // Find Schoolyear
    //     $schoolyear = $this->schoolyears->firstWhere('schoolyear', $this->data['schoolyear']);
    //     if (!$schoolyear) {
    //         throw new RowImportFailedException('No school year found');
    //     }

    //     // Find Student
    //     $student = $this->students->firstWhere('studentidn', $this->data['stud']);
    //     if (!$student) {
    //         throw new RowImportFailedException('No student idn found');
    //     }

    //     // Prepare Enrollment data
    //     $enrollmentData = [
    //         'stud_id' => $student->id,
    //         'schoolyear_id' => $schoolyear->id,
    //         'college_id' => $college->id,
    //         'program_id' => $program->id,
    //         'yearlevel_id' => $yearlevel->id,
    //     ];

    //     // Find or create Enrollment
    //     $enrollment = Enrollment::firstOrNew(
    //         ['stud_id' => $student->id, 'schoolyear_id' => $schoolyear->id],
    //         $enrollmentData
    //     );

    //     // Handle Semester and Collection relationships
    //     if ($schoolyear) {
    //         $semesters = $this->semesters->where('schoolyear_id', $schoolyear->id);

    //         // Prepare semester and collection relationships
    //         $semesterRelations = $semesters->map(function ($semester) {
    //             return ['semester_id' => $semester->id];
    //         });

    //         $collectionRelations = $semesters->flatMap(function ($semester) {
    //             return $this->collections
    //                 ->where('semester_id', $semester->id)
    //                 ->map(function ($collection) {
    //                     return ['collection_id' => $collection->id];
    //                 });
    //         });

    //         // Attach relationships to the Enrollment model
    //         $enrollment->semesters()->sync($semesterRelations->pluck('semester_id')->toArray());
    //         $enrollment->collections()->sync($collectionRelations->pluck('collection_id')->toArray());
    //     }

    //     // Save the Enrollment model with all relationships
    //     $enrollment->save();

    //     return $enrollment;
    // }

    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         Select::make('schoolyear_id')
    //             ->label('School Year')
    //             ->options(Schoolyear::pluck('schoolyear', 'id')) // Fetch school years
    //             ->required(), // Ensure the user selects a school year
    //     ];
    // }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('schoolyear_id')
                ->label('School Year')
                ->options(Schoolyear::pluck('schoolyear', 'id')) // Fetch school years
                ->required()
                ->afterStateUpdated(function ($state, callable $set) {
                    // ✅ Ensure a school year is selected
                    if (!$state) {
                        throw ValidationException::withMessages([
                            'schoolyear_id' => 'Please select a valid school year.',
                        ]);
                    }

                    // ✅ Check if the selected school year has semesters
                    $semesterCount = Semester::where('schoolyear_id', $state)->count();

                    if ($semesterCount <= 0) {
                        throw ValidationException::withMessages([
                            'schoolyear_id' => 'The selected school year does not have any associated semesters. Please add semesters before proceeding.',
                        ]);
                    }

                    // ✅ Check if any semester has valid collections with a positive amount
                    $hasValidCollections = Semester::where('schoolyear_id', $state)
                        ->whereHas('collections', function ($query) {
                            $query->where('amount', '>', 0);
                        })
                        ->exists();

                    if (!$hasValidCollections) {
                        throw ValidationException::withMessages([
                            'schoolyear_id' => 'The selected school year does not have any collections with valid amounts. Please configure collections correctly.',
                        ]);
                    }
                }),
        ];
    }

    public function resolveRecord(): ?Enrollment
    {
        $this->loadLookups();

        $schoolyearId = $this->options['schoolyear_id'] ?? null;

        if (!$schoolyearId) {
            throw new \Exception('No school year selected.');
        }

        $college = $this->colleges->firstWhere(function ($college) {
            return strtolower($college->college) === strtolower($this->data['college']);
        });

        if (! $college) {
            $college = College::create(['college' => $this->data['college']]);
            $this->colleges->push($college);
        }

        $program = $this->programs
            ->where('college_id', $college->id)
            ->firstWhere(function ($program) {
                return strtolower($program->program) === strtolower($this->data['program']);
            });
        if (! $program) {
            $program = Program::create(['college_id' => $college->id, 'program' => $this->data['program']]);
            $this->programs->push($program);
        }

        $yearlevel = $this->yearlevels
            ->where('program_id', $program->id)
            ->firstWhere('yearlevel', $this->data['yearlevel']);
        if (! $yearlevel) {
            $yearlevel = Yearlevel::create(['program_id' => $program->id, 'yearlevel' => $this->data['yearlevel']]);
            $this->yearlevels->push($yearlevel);
        }

        // $schoolyear = $this->schoolyears->firstWhere('schoolyear', $this->data['schoolyear']);
        // if (! $schoolyear) {
        //     throw new RowImportFailedException('No school year found');
        // }

        $student = $this->students->firstWhere('studentidn', $this->data['stud']);
        if (! $student) {
            throw new RowImportFailedException('No student idn found');
        }

        $enrollment = Enrollment::firstOrNew(
            [
                'stud_id' => $student->id,
                'schoolyear_id' => $schoolyearId,
            ],
            [
                'college_id' => $college->id,
                'program_id' => $program->id,
                'yearlevel_id' => $yearlevel->id,
            ]
        );

        $enrollment->save();

        $enrollmentId = $enrollment->id;

        if ($schoolyearId) {
            $semester = $this->semesters->where('schoolyear_id', $schoolyearId);
            $existingEntry = DB::table('enrollment_semester')->where('enrollment_id', $enrollmentId)->exists();

            if ($existingEntry) {
                DB::table('enrollment_semester')->where('enrollment_id', $enrollmentId)->delete();
            }

            foreach ($semester as $semester1) {
                DB::table('enrollment_semester')->insert([
                    'enrollment_id' => $enrollmentId,
                    'semester_id' => $semester1->id,
                ]);
            }

            if ($semester) {
                $existingEntry1 = DB::table('collection_enrollment')->where('enrollment_id', $enrollmentId)->exists();
                if ($existingEntry1) {
                    DB::table('collection_enrollment')->where('enrollment_id', $enrollmentId)->delete();
                }
                foreach ($semester as $semester1) {
                    $collection = $this->collections->where('semester_id', $semester1->id);
                    foreach ($collection as $collection1) {
                        DB::table('collection_enrollment')->insert([
                            'enrollment_id' => $enrollmentId,
                            'collection_id' => $collection1->id,
                        ]);
                    }
                }
            }
        }

        return $enrollment;
    }

    // public static function getCompletedNotificationBody(Import $import): string
    // {
    //     $recipient = auth()->user();
    //     $body = 'Your enrollment import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

    //     if ($failedRowsCount = $import->getFailedRowsCount()) {
    //         $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
    //         Notification::make()
    //             ->title('Enrollment Import')
    //             ->body($body)
    //             ->danger()
    //             ->sendToDatabase($recipient);
    //     }
    //     // } else {
    //     //     Notification::make()
    //     //         ->title('Enrollment Import')
    //     //         ->body($body)
    //     //         ->success()
    //     //         ->sendToDatabase($recipient);
    //     // }

    //     return $body;
    // }
    public static function getCompletedNotificationBody(Import $import): string
    {
        $recipient = auth()->user();

        // Base message
        $body = 'Your enrollment import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        // Check for failed rows
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $downloadUrl = url("/filament/imports/{$import->id}/failed-rows/download");
            // $downloadUrl = config('app.url') . "/filament/imports/{$import->id}/failed-rows/download";

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

        // Notification::make()
        //     ->title('Import completed')
        //     ->body($body)
        //     ->success()
        //     ->sendToDatabase($recipient);

        return $body;
    }
}
