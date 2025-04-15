<?php

namespace App\Jobs;

use App\Models\Pay;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;

class ExportPaymentRecordsJobsToPdf implements ShouldQueue
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

    public function handle()
    {
        $query = Pay::query();

        if ($this->date_from && $this->date_to) {
            $query->whereBetween('created_at', [$this->date_from, $this->date_to]);
        }

        $payments = $query->get();

        if ($payments->isNotEmpty()) {
            $pdf = SnappyPdf::loadView(
                'pdf.print_payment_records',
                [
                    'payments' => $payments,
                    'date_from' => $this->date_from->format('M d, Y'),
                    'date_to' => $this->date_to->format('M d, Y'),
                ]
            );

            $fileName = 'Student-Payment-Records-Export-' . now()->timestamp . '.pdf';
            $filePath = public_path("exports/{$fileName}");

            // Store file directly in public/exports
            file_put_contents($filePath, $pdf->output());

            Log::info("Export file stored at: " . $filePath);

            // ✅ Notify user
            if ($this->user) {
                Log::info("Sending notification to user: " . $this->user->id);

                $downloadUrl = url("exports/{$fileName}");

                Notification::make()
                    ->title('Student Payment Records Export Ready')
                    ->body(new HtmlString(
                        'Your student payment data export has been successfully completed. Click the link below to download your pdf file:<br><br>' .
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
