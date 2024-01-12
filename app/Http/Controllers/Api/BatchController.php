<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function get($id) {
        $course = Course::where('id', $id)->first();
        $batches = Batch::where('course_id', $id)->orderBy('start_date', 'ASC')->get();

        return response()->json([
            'course' => $course,
            'batches' => $batches,
        ]);
    }
    public function create($id, Request $request) {
        $saveData = Batch::create([
            'course_id' => $id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_quantity' => $request->quantity,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'message' => "ok"
        ]);
    }
}
