<?php

namespace App\Http\Controllers\Api;

use Str;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    public function get(Request $request) {
        $q = strtolower($request->q);
        $raw = Coupon::orderBy('created_at', 'DESC')->paginate(10);
        $coupons = $raw;

        foreach ($coupons as $c => $coup) {
            $forCourses = json_decode($coup->for_courses_id);
            $theCourses = [];
            $isFound = true;

            foreach ($forCourses as $fc) {
                $course = Course::where('id', $fc)->first(['id','title']);
                array_push($theCourses, $course);
            }

            $coupons[$c]['courses'] = $theCourses;
        }

        $coupons = json_decode(json_encode($coupons->collect()), true);

        if ($q != "") {
            foreach ($coupons as $c => $coup) {
                $isFound = false;
                foreach ($coup['courses'] as $cour) {
                    if (strpos(strtolower($cour['title']), $q) > 0) {
                        $isFound = true;
                    }
                }

                if (!$isFound) {
                    array_splice($coupons, $c, 1);
                }
            }
        }

        return response()->json([
            'coupons' => $coupons,
            'raw' => $raw,
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
    public function generateMass(Request $request) {
        $quantity = $request->quantity;

        for ($i = 0; $i < $quantity; $i++) {
            $code = Str::random(32);
            $saveData = Coupon::create([
                'code' => $code,
                'discount_type' => $request->discount_type,
                'discount_amount' => $request->discount_amount,
                'quantity' => 1,
                'start_quantity' => 1,
                'for_courses_id' => json_encode($request->for_courses_id),
            ]);
        }

        Log::info($quantity);
    }
}
