<?php

namespace App\Http\Controllers;

use App\Jobs\ExportPaymentRecordsJobsToPdf;
use App\Jobs\ExportStudentPaymentsJobPdf;
use App\Models\Enrollment;
use App\Models\InvoiceRecord;
use App\Models\Pay;
use App\Models\Stud;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public $payments;
    public $studentInfo;

    /**
     * Print the invoice
     *
     * @param  int  $id
     */
    public function printInvoice($id)
    {
        // Retrieve the enrollment record with relationships
        $payments = Enrollment::with([
            'pays',
            'stud',
            'program',
            'college',
            'schoolyear',
            'collections',
            'yearlevelpayments',
        ])->find($id);

        // Check if the record exists
        if ($payments) {
            // Log the invoice generation
            // InvoiceRecord::create([
            //     'user_id' => auth()->user()->id,
            //     'pay_id' => $id,
            // ]);

            // Fetch sibling information
            $siblingsInformation = Stud::with(['siblings' => function ($query) use ($payments) {
                $query->whereHas('stud.enrollments', function ($enrollmentQuery) use ($payments) {
                    $enrollmentQuery->where('schoolyear_id', $payments->schoolyear_id);
                });
            }])->find($payments->stud_id);

            // Fix encoding issue
            $html = view('pdf.print_invoice', compact('payments', 'siblingsInformation'))->render();
            $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

            $customPaper = [0, 0, 200, 800];

            // Generate the PDF with valid page size
            $pdf = Pdf::loadHTML($html)
                // ->setPaper('A4') // or use ->setOption('page-width', '80mm')->setOption('page-height', '150mm')
                ->setPaper($customPaper)
                ->setOption('page-width', '80mm')
                ->setOption('page-height', '150mm')
                ->setOptions([
                    'margin-top'    => 0,
                    'margin-right'  => 0,
                    'margin-bottom' => 0,
                    'margin-left'   => 0,
                ])
                ->setOption('encoding', 'UTF-8');

            // Stream the generated PDF
            return $pdf->stream('invoice_' . str_replace(' ', '_', $payments->stud->firstname) . str_replace(' ', '_', $payments->stud->middlename) . str_replace(' ', '_', $payments->stud->lastname) . '.pdf');
        } else {
            // Notify if no record exists
            Notification::make()
                ->title('No invoice record found!')
                ->danger()
                ->send();

            return redirect()->back();
        }
    }

    /**
     * Print the invoice
     *
     * @param  int $id
     * @param  $schoolYear
     */
    public function downloadInvoice($id, $schoolYear)
    {
        // dd($id, $schoolYear);
        // Fetch the student's information
        $this->studentInfo = Enrollment::where('id', $id)->first();
        if (!$this->studentInfo) return;

        // Query enrollments based on the selected school year
        $query = Enrollment::with([
            'pays',
            'stud',
            'program',
            'college',
            'schoolyear',
            'collections',
            'yearlevel',
            'yearlevelpayments',
        ])->where('stud_id', $this->studentInfo->stud_id);

        if ($schoolYear !== 'all') {
            $query->where('schoolyear_id', $schoolYear); // Filter by school year
        }

        $this->payments = $query->get();

        // Check if records exist
        if ($this->payments->isNotEmpty()) {
            // Log the invoice generation
            // InvoiceRecord::create([
            //     'user_id' => auth()->user()->id,
            //     'pay_id' => $id,
            // ]);

            // Define custom paper size (e.g., 5x5 inches)
            $customPaper = [0, 0, 300, 600];

            // Generate the PDF with a custom paper size
            $pdf = Pdf::loadView('pdf.new_print_invoices', ['payments' => $this->payments])
                ->setPaper($customPaper);

            // Download the PDF instead of streaming
            return $pdf->download('invoice_' . $id . '.pdf');
        } else {
            // Notify if no record exists
            Notification::make()
                ->title('No invoice record found!')
                ->danger()
                ->send();

            return redirect()->back();
        }
    }

    public function exportRecord(Request $request)
    {
        $schoolyear_id = $request->input('schoolyear_id');
        $college_id = $request->input('college_id');
        $program_id = $request->input('program_id');
        $yearlevel_id = $request->input('yearlevel_id');
        $status = $request->input('status');

        // Dispatch the job to the queue
        ExportStudentPaymentsJobPdf::dispatch(
            $schoolyear_id,
            $college_id,
            $program_id,
            $yearlevel_id,
            $status,
            auth()->user() // Pass authenticated user for notification
        );

        // If this is an AJAX request expecting JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Export is being processed. You will be notified when it is ready.',
                'success' => true
            ]);
        }

        Notification::make()
            ->title('PDF Export in Progress')
            ->body('Your student payment information export is being processed. You will be notified once it is ready for download.')
            ->info()
            ->color('info')
            ->send();

        // Return a redirect or view as needed
        return back()->with('status', 'Export Started');
    }


    // public function exportRecord(Request $request)
    // {
    //     $schoolyear_id = $request->input('schoolyear_id');
    //     $college_id = $request->input('college_id');
    //     $program_id = $request->input('program_id');
    //     $yearlevel_id = $request->input('yearlevel_id');
    //     $status = $request->input('status');

    //     // Start with a base query
    //     $query = Enrollment::query();

    //     // Add filters conditionally
    //     if ($schoolyear_id) {
    //         $query->where('schoolyear_id', $schoolyear_id);
    //     }

    //     if ($college_id) {
    //         $query->where('college_id', $college_id);
    //     }

    //     if ($program_id) {
    //         $query->where('program_id', $program_id);
    //     }

    //     if ($yearlevel_id) {
    //         $query->where('yearlevel_id', $yearlevel_id);
    //     }

    //     // Handle status filtering
    //     if ($status === 'paid') {
    //         $query->where('status', 'paid');
    //     } elseif ($status === 'not_paid') {
    //         $query->whereNull('status');
    //     }

    //     // Get the results
    //     $payments = $query->get(); // Use get() to retrieve ALL records

    //     // Check if any records exist
    //     if ($payments->isNotEmpty()) {

    //         // Generate the PDF with all records using SnappyPdf

    //         $pdf = SnappyPdf::loadView('pdf.print_report', ['payments' => $payments]);

    //         // Stream the PDF to the browser
    //         return $pdf->download('Student-Payment-Information-Export.pdf');
    //     } else {
    //         // Notify if no record exists
    //         Notification::make()
    //             ->title('No invoice record found!')
    //             ->danger()
    //             ->send();

    //         return redirect()->back();
    //     }
    // }

    // public function exportRecordtoPdf(Request $request)
    // {
    //     $startDate = $request->input('startDate');
    //     $endDate = $request->input('endDate');

    //     $date_from = null;
    //     $date_to = null;

    //     // Ensure both startDate and endDate are provided
    //     if (!empty($startDate) && !empty($endDate)) {
    //         try {
    //             // Check if the format is already Y-m-d
    //             $date_from = \Carbon\Carbon::parse(trim($startDate))->startOfDay();
    //             $date_to = \Carbon\Carbon::parse(trim($endDate))->endOfDay();
    //         } catch (\Exception $e) {
    //             Log::error('Invalid date format: ' . $startDate . ' - ' . $endDate);
    //             return redirect()->back()->withErrors(['error' => 'Invalid date format.']);
    //         }
    //     }

    //     // Start with a base query
    //     $query = Pay::query();

    //     // Apply date filter if both dates are valid
    //     if ($date_from && $date_to) {
    //         $query->whereBetween('created_at', [$date_from, $date_to]);
    //     }

    //     // Get the results
    //     $payments = $query->get();

    //     // Check if any records exist
    //     if ($payments->isNotEmpty()) {
    //         $pdf = SnappyPdf::loadView(
    //             'pdf.print_payment_records',
    //             [
    //                 'payments' => $payments,
    //                 'date_from' => $date_from->format('M d, Y'),
    //                 'date_to' => $date_to->format('M d, Y'),
    //             ]
    //         );

    //         return $pdf->download('Student-Payment-Records-Export.pdf');
    //     } else {
    //         Notification::make()
    //             ->title('No invoice record found!')
    //             ->danger()
    //             ->send();

    //         return redirect()->back();
    //     }
    // }

    public function exportRecordtoPdf(Request $request)
    {
        $user = auth()->user();

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $date_from = null;
        $date_to = null;

        if (!empty($startDate) && !empty($endDate)) {
            try {
                $date_from = \Carbon\Carbon::parse(trim($startDate))->startOfDay();
                $date_to = \Carbon\Carbon::parse(trim($endDate))->endOfDay();
            } catch (\Exception $e) {
                Log::error('Invalid date format: ' . $startDate . ' - ' . $endDate);
                return redirect()->back()->withErrors(['error' => 'Invalid date format.']);
            }
        }

        // ✅ Dispatch job to queue
        ExportPaymentRecordsJobsToPdf::dispatch($user, $date_from, $date_to);

        // ✅ Notify user export is in progress
        Notification::make()
            ->title('PDF Export in Progress')
            ->body('Your student payment records export is being processed. You will be notified once it is ready for download.')
            ->info()
            ->color('info')
            ->send();

        return back();
    }


    public function exportRecordAll()
    {
        // Retrieve all enrollment records that match the schoolyear_id
        $payments = Enrollment::all(); // Use get() to retrieve ALL records

        // Check if any records exist
        if ($payments->isNotEmpty()) {

            // Generate the PDF with all records using SnappyPdf
            $pdf = SnappyPdf::loadView('pdf.print_report', ['payments' => $payments]);

            // Stream the PDF to the browser
            return $pdf->inline();
        } else {
            // Notify if no record exists
            Notification::make()
                ->title('No invoice record found!')
                ->danger()
                ->send();

            return redirect()->back();
        }
    }
}
