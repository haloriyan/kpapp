<?php

namespace App\Http\Controllers\Api;

use App\Exports\EnrollExport;
use App\Http\Controllers\Controller;
use App\Models\Enroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function enroll() {
        $now = Carbon::now();
        $filename = "Enroll Data - Exported on " . $now->format('d M Y_H:i:s') . '.xlsx';
        $datas = Enroll::orderBy('created_at', 'DESC')->with(['course', 'user'])->get();

        return Excel::download(new EnrollExport([
            'datas' => $datas
        ]), $filename);
    }
}
