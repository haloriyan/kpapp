<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enroll;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function enroll(Request $request) {
        $filter = [];

        $query = Enroll::orderBy('created_at', 'DESC');

        if ($request->q != "") {
            $query = $query->whereHas('course', function ($q) use ($request) {
                $q->where('title', 'LIKE', '%'.$request->q.'%');
            })->orWhereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%'.$request->q.'%');
            });
        }

        $datas = $query->with(['course', 'user'])
        ->paginate(50);
        
        return response()->json([
            'status' => 200,
            'datas' => $datas,
        ]);
    }
}
