<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function download(Request $request)
    {
        $file = $request->query('file');

        if (!Storage::exists($file)) {
            abort(404, 'File not found.');
        }

        return Storage::download($file);
    }
}
