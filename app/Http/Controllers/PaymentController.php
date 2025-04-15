<?php

namespace App\Http\Controllers;

use App\Exports\PaymentExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    /**
     * Print the invoice
     *
     * @param  int  $id
     */

     public function export($id = null)
     {
         $year = !empty($id) ? (int) $id : null; // Convert to int if not empty, otherwise set to null
         return Excel::download(new PaymentExport($year), 'Student-Payment-Remaining-Balance.xlsx', \Maatwebsite\Excel\Excel::XLSX);
     }
}
