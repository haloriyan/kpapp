<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function getByID($id, Request $request) {
        $query = Course::where('id', $id);
        if ($request->with != null) {
            $query = $query->with($request->with);
        }
        $course = $query->first();

        return response()->json([
            'course' => $course
        ]);
    }
    public function get(Request $request) {
        $courses = Course::where($request->filter)->get();
        
        return response()->json([
            'courses' => $courses,
        ]);
    }
    public function create(Request $request) {
        $user = User::where('token', $request->token)->first();
        $cover = $request->file('cover_image');
        $coverFileName = $cover->getClientOriginalName();

        $saveData = Course::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'cover_image' => $coverFileName,
            'category' => $request->category,
            'price' => 0,
        ]);

        $cover->storeAs('public/cover_images', $coverFileName);

        return response()->json([
            'message' => "Berhasil membuat course baru",
            'course' => $saveData,
        ]);
    }
    public function update($id, Request $request) {
        $data = Course::where('id', $id);
        $course = $data->first();

        $toUpdate = [
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
        ];

        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $coverFileName = $cover->getClientOriginalName();
            $toUpdate['cover_image'] = $coverFileName;
            if ($course->cover_image != null) {
                $deleteOldCover = Storage::delete('public/cover_images/' . $course->cover_image);
            }
            $cover->storeAs('public/cover_images', $coverFileName);
        }

        $updateData = $data->update($toUpdate);

        return response()->json([
            'status' => 200,
            'message' => "Berhasil memperbarui data pelatihan"
        ]);
    }
    public function delete(Request $request) {
        $data = Course::where('id', $request->id);
        $course = $data->with(['medias', 'materials'])->first();

        $deleteData = $data->delete();
        $deleteCover = Storage::delete('public/cover_images/' . $course->cover_image);

        foreach ($course->medias as $media) {
            $deleteMedia = Storage::delete('public/medias/' . $media->filename);
        }
        foreach ($course->materials as $material) {
            $deleteVideo = Storage::delete('public/video_materials/' . $material->filename);
            $deleteThumbnail = Storage::delete('public/thumbs/' . $material->thumbnail);
        }

        return response()->json([
            'message' => "Berhasil membuat course baru"
        ]);
    }
    public function search(Request $request) {
        $courses = Course::where('title', 'LIKE', '%'.$request->q.'%')->get();

        return response()->json([
            'courses' => $courses,
        ]);
    }
    public function enroll($id, Request $request) {
        $c = Coupon::where('code', $request->code);
        $coupon = $c->first();
        $status = 500;
        $message = "Kupon tidak valid";
        $ableToEnroll = false;
        
        if ($coupon != null) {
            if ($coupon->for_courses_id != null) {
                $forCourses = json_decode($coupon->for_courses_id);
                if (in_array($id, $forCourses)) {
                    $ableToEnroll = true;
                }
            } else {
                $ableToEnroll = true;
            }
        }

        if ($ableToEnroll) {
            $c->decrement('quantity');

            $user = User::where('token', $request->token)->first();

            $saveData = Enroll::create([
                'coupon_id' => $coupon->id,
                'course_id' => $id,
                'user_id' => $user->id,
                'payment_status' => "PAID",
                'is_completed' => false,
            ]);

            $message = "Berhasil enroll pelatihan";
            $status = 200;
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }
    public function dashboard($courseID) {
        $course = Course::where('id', $courseID)->first();
        $enrolls = Enroll::where('course_id', $courseID)->get();
        $completed = [];
        foreach ($enrolls as $enroll) {
            if ($enroll->is_completed) {
                array_push($completed, $enroll);
            }
        }

        return response()->json([
            'course' => $course,
            'enrolls' => $enrolls,
            'completed' => $completed,
        ]);
    }
}
