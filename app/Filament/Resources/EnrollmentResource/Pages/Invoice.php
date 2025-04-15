<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Models\Enrollment;
use App\Models\Stud;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class Invoice extends Page
{
    protected static string $resource = EnrollmentResource::class;

    protected static string $view = 'filament.resources.enrollment-resource.pages.invoice';

    public $record;

    public $payments;

    public $siblingsInformation;

    public function mount($record)
    {
        $this->record = $record;
        $this->payments = Enrollment::with([
            'pays',
            'stud',
            'program',
            'college',
            'schoolyear',
            'collections',
            'yearlevelpayments'
        ])->find($record);

        // $this->siblingsInformation = Stud::with(['siblings'])->find($this->payments->stud_id);
        $this->siblingsInformation = Stud::with(['siblings' => function ($query) {
            $query->whereHas('stud.enrollments', function ($enrollmentQuery) {
                $enrollmentQuery->where('schoolyear_id', $this->payments->schoolyear_id);
            });
        }])->find($this->payments->stud_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('return')
                ->color('primary')
                ->icon('heroicon-m-arrow-left-circle')
                ->label('Go back')
                ->livewireClickHandlerEnabled()
                ->url(redirect()->back()->getTargetUrl()),
            Action::make('print')
                ->color('success')
                ->icon('heroicon-m-printer')
                ->label('Print')
                ->url(route('PRINT.INVOICE', ['id' => $this->record]), true)
                ->extraAttributes([
                    'target' => '_blank', // Open in new window
                    'onclick' => 'window.open(this.href, "_blank", "width=800,height=600"); return false;',
                ]),

        ];
    }
}
