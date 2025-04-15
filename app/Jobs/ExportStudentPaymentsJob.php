<?php

namespace App\Jobs;

use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ExportStudentPaymentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schoolYearId;
    protected $userId;

    public function __construct($schoolYearId, $userId)
    {
        $this->schoolYearId = $schoolYearId;
        $this->userId = $userId;
    }

    public function handle()
    {
        // Retrieve all the payment records for the given school year
        $payments = Enrollment::where('schoolyear_id', $this->schoolYearId)->get();

        // Generate the PDF with all the data
        $pdf = Pdf::loadView('pdf.print_report', ['payments' => $payments])
            ->setPaper('a4', 'portrait');

        // Store the PDF content in a temporary file
        $fileName = 'student_payments_' . time() . '.pdf';
        $path = storage_path('app/public/' . $fileName);

        // Save the PDF content to file
        file_put_contents($path, $pdf->output());

        // Store the file path in the session to retrieve it later
        Session::put('pdf_path', $fileName);
    }
}
