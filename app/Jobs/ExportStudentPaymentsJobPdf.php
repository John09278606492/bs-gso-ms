<?php

namespace App\Jobs;

use App\Models\Enrollment;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;

class ExportStudentPaymentsJobPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schoolyear_id;
    protected $college_id;
    protected $program_id;
    protected $yearlevel_id;
    protected $status;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($schoolyear_id, $college_id, $program_id, $yearlevel_id, $status, $user)
    {
        $this->schoolyear_id = $schoolyear_id;
        $this->college_id = $college_id;
        $this->program_id = $program_id;
        $this->yearlevel_id = $yearlevel_id;
        $this->status = $status;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $query = Enrollment::query();

        if ($this->schoolyear_id) {
            $query->where('schoolyear_id', $this->schoolyear_id);
        }

        if ($this->college_id) {
            $query->where('college_id', $this->college_id);
        }

        if ($this->program_id) {
            $query->where('program_id', $this->program_id);
        }

        if ($this->yearlevel_id) {
            $query->where('yearlevel_id', $this->yearlevel_id);
        }

        if ($this->status === 'paid') {
            $query->where('status', 'paid');
        } elseif ($this->status === 'not_paid') {
            $query->whereNull('status');
        }

        $payments = $query->get();

        if ($payments->isNotEmpty()) {
            $pdf = SnappyPdf::loadView('pdf.print_report', ['payments' => $payments]);

            $fileName = 'Student-Payment-Information-Export-' . now()->timestamp . '.pdf';
            $filePath = public_path("exports/{$fileName}");

            // ✅ Ensure public/exports directory exists
            if (!file_exists(public_path('exports'))) {
                mkdir(public_path('exports'), 0777, true);
            }

            // ✅ Store the file directly in public/exports
            file_put_contents($filePath, $pdf->output());

            Log::info("Export file stored at: " . $filePath);

            // ✅ Generate the public URL
            $downloadUrl = url("exports/{$fileName}");

            // ✅ Notify the user when export is complete
            if ($this->user) {
                Log::info("Sending notification to user: " . $this->user->id);

                Notification::make()
                    ->title('Student Payment Information Export Ready')
                    ->body(new HtmlString(
                        'Your student payment data export has been successfully completed. Click the link below to download your PDF file:<br><br>' .
                            '<a href="' . $downloadUrl . '" download style="color: red; font-weight: bold; text-decoration: underline;">
                                Download PDF File
                            </a>'
                    ))
                    ->success()
                    ->sendToDatabase($this->user, isEventDispatched: true);
            } else {
                Log::error("User not found while sending export notification.");
            }
        } else {
            Notification::make()
                ->title('No invoice record found!')
                ->danger()
                ->sendToDatabase($this->user);
        }
    }
}
