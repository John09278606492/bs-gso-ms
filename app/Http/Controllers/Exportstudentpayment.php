<?php

namespace App\Http\Controllers;

use App\Exports\AllStudentExport;
use App\Exports\PaymentRecordExport;
use App\Exports\StudentpaymentExport;
use App\Jobs\ExportPaymentRecordsJobs;
use App\Jobs\ExportStudentPayments;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class Exportstudentpayment extends Controller
{
    /**
     * Export student payments based on filters
     *
     * @param  int|null  $schoolyear_id
     * @param  int|null  $college_id
     * @param  int|null  $program_id
     * @param  int|null  $yearlevel_id
     * @param  string|null  $status
     */
    // public function export(Request $request)
    // {
    //     $schoolyear_id = $request->input('schoolyear_id');
    //     $college_id = $request->input('college_id');
    //     $program_id = $request->input('program_id');
    //     $yearlevel_id = $request->input('yearlevel_id');
    //     $status = $request->input('status');

    //     return Excel::download(
    //         new StudentpaymentExport(
    //             $schoolyear_id ? (int)$schoolyear_id : null,
    //             $college_id ? (int)$college_id : null,
    //             $program_id ? (int)$program_id : null,
    //             $yearlevel_id ? (int)$yearlevel_id : null,
    //             $status
    //         ),
    //         'Student-Payment-Information-Export.xlsx',
    //         \Maatwebsite\Excel\Excel::XLSX
    //     );
    // }

    // public function export(Request $request)
    // {
    //     $user = auth()->user();

    //     $schoolyear_id = $request->input('schoolyear_id');
    //     $college_id = $request->input('college_id');
    //     $program_id = $request->input('program_id');
    //     $yearlevel_id = $request->input('yearlevel_id');
    //     $status = $request->input('status');

    //     $fileName = 'Student-Payment-Information-Export-' . now()->timestamp . '.xlsx';
    //     $filePath = 'exports/' . $fileName;

    //     // Notify user export is starting
    //     Notification::make()
    //         ->title('Export in Progress')
    //         ->body('Your student payment data export is being processed. You will be notified once it is ready for download.')
    //         ->info()
    //         ->send();

    //     // ✅ FIX: Pass the correct parameters to queue() method
    //     Excel::queue(new StudentpaymentExport(
    //         $schoolyear_id ? (int)$schoolyear_id : null,
    //         $college_id ? (int)$college_id : null,
    //         $program_id ? (int)$program_id : null,
    //         $yearlevel_id ? (int)$yearlevel_id : null,
    //         $status
    //     ), $fileName, 'public') // ✅ Added the file name and storage disk
    //         ->chain([
    //             function () use ($user, $filePath) {
    //                 $downloadUrl = Storage::url($filePath);

    //                 // Send success notification with download link
    //                 Notification::make()
    //                     ->title('Export Completed')
    //                     ->body(new \Illuminate\Support\HtmlString(
    //                         '<a href="' . $downloadUrl . '" download class="text-sm font-semibold text-green-600 dark:text-green-400 hover:underline">
    //                     Click here to download your file
    //                 </a>'
    //                     ))
    //                     ->success()
    //                     ->sendToDatabase($user);
    //             }
    //         ]);

    //     return back();
    // }

    public function export(Request $request)
    {
        $schoolyear_id = $request->input('schoolyear_id');
        $college_id = $request->input('college_id');
        $program_id = $request->input('program_id');
        $yearlevel_id = $request->input('yearlevel_id');
        $status = $request->input('status');
        $user = auth()->user(); // Get the logged-in user

        // Dispatch the job
        // ExportStudentPayments::dispatch(
        //     $user,
        //     $schoolyear_id ? (int)$schoolyear_id : null,
        //     $college_id ? (int)$college_id : null,
        //     $program_id ? (int)$program_id : null,
        //     $yearlevel_id ? (int)$yearlevel_id : null,
        //     $status
        // );

        // Notification::make()
        //     ->title('EXCEL Export in Progress')
        //     ->body('Your student payment information export is being processed. Please wait for a moment.')
        //     ->info()
        //     ->color('info')
        //     ->send();

        $fileName = 'Student-Payment-Records-Export-' . now()->format('Y-m-d_H-i') . '.xlsx';

        return (new StudentpaymentExport(
            $schoolyear_id ? (int)$schoolyear_id : null,
            $college_id ? (int)$college_id : null,
            $program_id ? (int)$program_id : null,
            $yearlevel_id ? (int)$yearlevel_id : null,
            $status
        ))->download($fileName);

        // If this is an AJAX request expecting JSON
        // if ($request->expectsJson() || $request->ajax()) {
        //     return response()->json([
        //         'message' => 'Export is being processed. You will be notified when it is ready.',
        //         'success' => true
        //     ]);
        // }

        // Notification::make()
        //     ->title('EXCEL Export in Progress')
        //     ->body('Your student payment information export is being processed. You will be notified once it is ready for download.')
        //     ->info()
        //     ->color('info')
        //     ->send();

        // Return a redirect or view as needed
        // return back()->with('status', 'Export Started');
    }

    public function exportPaymentRecord(Request $request)
    {
        $user = auth()->user();

        $startDate = $request->input('date_from');
        $endDate = $request->input('date_to');

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

        $fileName = 'Student-Payment-Records-Export-' . now()->format('Y-m-d_H-i') . '.xlsx';

        // Notification::make()
        //     ->title('EXCEL Export in Progress')
        //     ->body('Your student payment records export is being processed. Please wait for a moment.')
        //     ->info()
        //     ->color('info')
        //     ->send();


        return (new PaymentRecordExport($date_from, $date_to))->download($fileName);
        // ✅ Dispatch job
        // ExportPaymentRecordsJobs::dispatch($user, $date_from, $date_to);

        // ✅ Notify user that export is processing

        // Notification::make()
        //     ->title('EXCEL Export in Progress')
        //     ->body('Your student payment records export is being processed. You will be notified once it is ready for download.')
        //     ->info()
        //     ->color('info')
        //     ->send();

        // return back();
    }
}
