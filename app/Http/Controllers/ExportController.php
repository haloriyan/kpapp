<?php

namespace App\Http\Controllers;

use App\Exports\EnrollExport;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
    public function participant() {
        $now = Carbon::now();
        $filename = "Kelas Personalia User - Exported on " . $now->format('d M Y_H:i:s') . '.xlsx';
        $datas = User::where('role', 'user')->orderBy('created_at', 'DESC')->get();

        return Excel::download(new EnrollExport([
            'datas' => $datas
        ]), $filename);
    }
    public function certificate($enrollID) {
        $enroll = Enroll::where('id', $enrollID)->with(['course.certificate', 'user'])->first();

        $pdf = Pdf::loadView('pdf.certificate', [
            'enroll' => $enroll,
        ])->setPaper('a4', 'landscape')
        ->set_option('isRemoteEnabled', true);

        return $pdf->stream();

        // return $enroll;
    }
    public function certificatePreview($courseID) {
        $course = Course::where('id', $courseID)->with('certificate')->first();
        $pdf = Pdf::loadView('pdf.certificate_prev', [
            'course' => $course
        ])->setPaper('a4', 'landscape')->set_option('isRemoteEnabled', true);

        return $pdf->stream();
    }
}
