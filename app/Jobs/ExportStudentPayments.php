<?php

namespace App\Jobs;

use App\Exports\StudentpaymentExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class ExportStudentPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $schoolyear_id;
    protected $college_id;
    protected $program_id;
    protected $yearlevel_id;
    protected $status;

    public function __construct($user, $schoolyear_id, $college_id, $program_id, $yearlevel_id, $status)
    {
        $this->user = $user;
        $this->schoolyear_id = $schoolyear_id;
        $this->college_id = $college_id;
        $this->program_id = $program_id;
        $this->yearlevel_id = $yearlevel_id;
        $this->status = $status;
    }

    public function handle()
    {
        $fileName = 'Student-Payment-Information-Export-' . now()->timestamp . '.xlsx';
        $tempPath = 'exports/' . $fileName;

        // ✅ Store the file temporarily in storage/app/exports/
        Excel::store(new StudentpaymentExport(
            $this->schoolyear_id,
            $this->college_id,
            $this->program_id,
            $this->yearlevel_id,
            $this->status
        ), $tempPath, 'local');

        // ✅ Move the file to public/exports directory
        $storagePath = storage_path('app/' . $tempPath);
        $publicPath = public_path('exports/' . $fileName);

        // ✅ Ensure the public/exports directory exists
        if (!file_exists(public_path('exports'))) {
            mkdir(public_path('exports'), 0777, true);
        }

        // ✅ Move the file to public_path
        if (file_exists($storagePath)) {
            rename($storagePath, $publicPath);
            Log::info("Export file moved to public path: " . $publicPath);
        } else {
            Log::error("Export file not found at: " . $storagePath);
        }

        // ✅ Generate the public URL
        $downloadUrl = asset('exports/' . $fileName);

        // ✅ Notify the user using Filament's built-in notification
        if ($this->user) {
            Log::info("Sending notification to user: " . $this->user->id);

            Notification::make()
                ->title('Student Payment Information Export Ready')
                ->body(new HtmlString(
                    'Your student payment data export has been successfully completed. Click the link below to download your Excel file:<br><br>' .
                        '<a href="' . $downloadUrl . '" download style="color: green; font-weight: bold; text-decoration: underline;">
                        Download EXCEL File
                    </a>'
                ))
                ->success()
                ->sendToDatabase($this->user, isEventDispatched: true);
        } else {
            Log::error("User not found while sending export notification.");
        }
    }
}
