<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function get(Request $request) {
        $coupons = Coupon::orderBy('created_at', 'DESC')->get();
        $courses = [];

        if ($request->with_courses == "y") {
            $courses = Course::orderBy('title', 'ASC')->get(['id', 'title']);
        }

        return response()->json([
            'coupons' => $coupons,
            'courses' => $courses,
        ]);
    }
    public function create(Request $request) {
        $saveData = Coupon::create([
            'code' => $request->code,
            'discount_type' => $request->discount_type,
            'discount_amount' => $request->discount_amount,
            'quantity' => $request->quantity,
            'start_quantity' => $request->quantity,
            'for_courses_id' => json_encode($request->for_courses_id),
        ]);

        return response()->json([
            'message' => "Berhasil membuat kupon baru"
        ]);
    }
}
