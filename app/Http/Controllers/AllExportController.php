<?php

namespace App\Http\Controllers;

use App\Exports\AllStudentExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AllExportController extends Controller
{
    /**
     * Export all student payments with optional filters
     *
     * @param  int|null  $college_id
     * @param  int|null  $program_id
     * @param  int|null  $yearlevel_id
     * @param  string|null  $status
     */
    public function allStudentExport(
        $college_id = null,
        $program_id = null,
        $yearlevel_id = null,
        $status = null
    ) {
        return Excel::download(
            new AllStudentExport($college_id, $program_id, $yearlevel_id, $status),
            'All-Student-Payment-Remaining-Balance.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
