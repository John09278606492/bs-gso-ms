<?php

namespace App\Jobs;

use App\Exports\PaymentRecordExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;

class ExportPaymentRecordsJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $date_from;
    protected $date_to;

    public function __construct($user, $date_from, $date_to)
    {
        $this->user = $user;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
    }

    // public function handle()
    // {
    //     $fileName = 'Student-Payment-Records-Export-' . now()->timestamp . '.xlsx';
    //     $filePath = 'exports/' . $fileName;

    //     // ✅ Store the export file in the public disk
    //     Excel::store(new PaymentRecordExport($this->date_from, $this->date_to), $filePath, 'public');

    //     Log::info("Export file stored at: " . $filePath);

    //     if ($this->user) {
    //         Log::info("Sending notification to user: " . $this->user->id);

    //         // ✅ Correctly generate the download URL
    //         $downloadUrl = Storage::url($filePath);
    //         // $downloadUrl = asset('storage/' . $filePath);

    //         Log::info("Download URL: " . $downloadUrl);
    //         dd($downloadUrl);

    //         Notification::make()
    //             ->title('Student Payment Records Export Ready')
    //             ->body(new HtmlString(
    //                 'Your student payment data export has been successfully completed. Click the link below to download your excel file:<br><br>' .
    //                     '<a href="' . $downloadUrl . '" download style="color: green; font-weight: bold; text-decoration: underline;">
    //             Download EXCEL File
    //         </a>'
    //             ))
    //             ->success()
    //             ->sendToDatabase($this->user, isEventDispatched: true);
    //     } else {
    //         Log::error("User not found while sending export notification.");
    //     }
    // }


    public function handle()
    {
        $fileName = 'Student-Payment-Records-Export-' . now()->timestamp . '.xlsx';
        $tempPath = 'exports/' . $fileName;

        // ✅ Store the export temporarily in storage
        Excel::store(new PaymentRecordExport($this->date_from, $this->date_to), $tempPath, 'local');

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

        // ✅ Notify the user
        if ($this->user) {
            Log::info("Sending notification to user: " . $this->user->id);

            Notification::make()
                ->title('Student Payment Records Export Ready')
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
