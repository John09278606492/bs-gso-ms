<?php

namespace App\Filament\Pages;

use App\Models\Enrollment;
use App\Models\Schoolyear;
use App\Models\Sibling;
use App\Models\Stud;
use Carbon\Carbon;
use Filament\Pages\Page;

class StudentInformation extends Page
{
    public $activeTab = 'student info';
    public $studentIDN;
    public $studentInfo = null;
    public $sibling_id;
    public $finalSib;
    public $defaultSchoolYearId;
    public $today;
    public $enrollmentId;
    public $searchStudent = '';
    public $students = [];
    public $selectedSchoolYear;
    public $schoolYears = [];
    public $payments = null;
    public $siblingsInformation = null;
    public $studentSchoolyear = null;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.student-information';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->role == 'guest';
    }

    public static function canAccess(): bool
    {
        return auth()->user()->role == 'guest';
    }

    public function mount()
    {
        $this->studentIDN = auth()->user()->canId ?? null;
        if (!$this->studentIDN) return;

        $this->schoolYears = Schoolyear::whereHas('enrollments', function ($query) {
            $query->where('stud_id', Stud::where('studentidn', $this->studentIDN)->value('id'));  // Filter by student ID
        })->get();

        $this->today = Carbon::today();

        // 🔹 Set default school year (based on the current date)
        $this->defaultSchoolYearId = Schoolyear::where('startDate', '<=', $this->today)
            ->where('endDate', '>=', $this->today)
            ->value('id');

        // If there's no active school year, use the latest one available
        if (!$this->defaultSchoolYearId) {
            $this->defaultSchoolYearId = Schoolyear::latest('startDate')->value('id');
        }

        // Set selected school year to default
        $this->selectedSchoolYear = $this->defaultSchoolYearId;

        // 🔹 Load student data based on the default school year
        $this->loadStudentData();
    }

    // Trigger when the selected school year is updated
    public function updatedSelectedSchoolYear($value)
    {
        // Update the school year ID and re-fetch the relevant data
        $this->defaultSchoolYearId = $value;

        // Re-fetch the necessary data (e.g., payments, invoice, etc.)
        $this->loadStudentData();
    }

    private function loadStudentData()
    {
        if (!$this->studentIDN) return;

        // Fetch the student's information
        $this->studentInfo = Stud::with('siblings')->where('studentidn', $this->studentIDN)->first();
        if (!$this->studentInfo) return;

        $this->studentSchoolyear = Enrollment::with([
            'pays',
            'stud',
            'program',
            'college',
            'schoolyear',
            'collections',
            'yearlevel',
            'yearlevelpayments',
        ])
            ->where('stud_id', $this->studentInfo->id)
            ->get();

        if ($this->selectedSchoolYear === 'all') {
            // Retrieve all enrollments associated with this student
            $this->payments = Enrollment::with([
                'pays',
                'stud',
                'program',
                'college',
                'schoolyear',
                'collections',
                'yearlevel',
                'yearlevelpayments',
            ])
                ->where('stud_id', $this->studentInfo->id)
                ->get();
        } else {
            // Retrieve enrollments for a specific school year
            $this->enrollmentId = Enrollment::where('stud_id', $this->studentInfo->id)
                ->where('schoolyear_id', $this->defaultSchoolYearId)
                ->value('id');

            if (!$this->enrollmentId) return;

            $this->payments = Enrollment::with([
                'pays',
                'stud',
                'program',
                'college',
                'schoolyear',
                'collections',
                'yearlevel',
                'yearlevelpayments',
            ])->where('id', $this->enrollmentId)->get();
        }
    }


    // // Your existing method to load the student data, including invoice and payments
    // private function loadStudentData()
    // {
    //     if (!$this->defaultSchoolYearId) return;

    //     // Fetch the student's information
    //     $this->studentInfo = Stud::with('siblings')->where('studentidn', $this->studentIDN)->first();
    //     if (!$this->studentInfo) return;

    //     // Get the enrollment ID based on the selected school year
    //     $this->enrollmentId = Enrollment::where('stud_id', $this->studentInfo->id)
    //         ->where('schoolyear_id', $this->defaultSchoolYearId)
    //         ->value('id');

    //     if (!$this->enrollmentId) return;

    //     // Fetch the payment details for the selected school year
    //     $this->payments = Enrollment::with([
    //         'pays',
    //         'stud',
    //         'program',
    //         'college',
    //         'schoolyear',
    //         'collections',
    //         'yearlevel',
    //         'yearlevelpayments',
    //     ])->find($this->enrollmentId);

    //     if (!$this->payments) return;

    //     // Fetch siblings and related data for the selected school year
    //     $this->siblingsInformation = Sibling::where('stud_id', $this->payments->stud_id)->value('sibling_id');
    //     $this->finalSib = Enrollment::with(['stud'])
    //         ->where('stud_id', $this->siblingsInformation)
    //         ->where('schoolyear_id', $this->defaultSchoolYearId)
    //         ->get();
    // }
}
