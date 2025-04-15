<?php

use App\Filament\Pages\StudentInformation;
use App\Http\Controllers\AllExportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Exportstudentpayment;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Models\Pay;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/admin'); // Change '/admin' to the correct URL of your panel
});

// Route::get('/', function () {
//     return view('welcome');
// })->name('welcome');

Route::get('/test', function () {
    return "Hello from Laravel!";
});

Route::get('/student-information', StudentInformation::class)->name('student.information');

// Route::get('/student-information', StudentInformation::class)->name('student_information');

Route::get('/receipt/{pay}', function (Pay $pay) {
    $receiptData = [
        'id' => $pay->id,
        'amount_formatted' => 'PHP' . number_format($pay->amount, 2),
        'status' => $pay->status,
        'date' => $pay->created_at->format('M. d, Y g:i a'),
        'student' => $pay->enrollment->stud->only(['id', 'lastname', 'firstname', 'middlename']),
    ];

    return view('receipts.payment', $receiptData);
})->name('generate-receipt');

Route::get('/print-invoice/{id}', [InvoiceController::class, 'printInvoice'])
    ->name('PRINT.INVOICE');

// Route::get('/export-records/{id?}', [InvoiceController::class, 'exportRecord'])
//     ->name('EXPORT.RECORDS');

Route::get('/export-records', [InvoiceController::class, 'exportRecord'])
    ->name('EXPORT.RECORDS');

Route::get('/export-records-pdf', [InvoiceController::class, 'exportRecordtoPdf'])
    ->name('EXPORT.PAYMENT.RECORDS.PDF');

Route::get('/export-payment-records', [Exportstudentpayment::class, 'exportPaymentRecord'])
    ->name('EXPORT.PAYMENT.RECORDS');

Route::get('/export-records-all', [InvoiceController::class, 'exportRecordAll'])
    ->name('EXPORT.RECORDS.ALL');

Route::get('/download-invoice/{id}/{schoolYear}', [InvoiceController::class, 'downloadInvoice'])
    ->name('PRINT.INVOICE.DOWNLOAD');

Route::get('/students-export', [Exportstudentpayment::class, 'export'])
    ->name('EXPORT.STUDENT.PAYMENT');

Route::get('/students-export-payment/{college_id?}/{program_id?}/{yearlevel_id?}/{status?}',        [AllExportController::class, 'allStudentExport'])
    ->name('ALL.STUDENT.PAYMENT');

Route::get('/students-payments-records', [PaymentController::class, 'export'])
    ->name('EXPORT.STUDENT.PAYMENT.RECORD');

// Route::get('/exports/download', [ExportController::class, 'download'])->name('exports.download');

Route::get('/exports/{file}/download', function ($file) {
    $filePath = 'exports/' . $file;

    if (!Storage::disk('local')->exists($filePath)) {
        abort(404);
    }

    return Response::download(storage_path('app/' . $filePath));
})->name('exports.download');
