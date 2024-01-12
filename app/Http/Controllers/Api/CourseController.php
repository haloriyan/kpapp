<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\EnrollPath;
use App\Models\Modul;
use App\Models\Presence;
use App\Models\QuestionAnswer;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        if ($request->q != "") {
            $query = $query->whereHas('enrolls', function ($quer) use ($request) {
                return $quer->whereHas('user', function ($q) use ($request) {
                    // Log::info('searching : ' . $request->q);
                    return $q->where('name', 'LIKE', '%'.$request->q.'%');
                });
            });
        }
        $course = $query->first();

        if ($request->user_id != null) {
            $enroll = Enroll::where([
                ['user_id', $request->user_id],
                ['course_id', $course->id]
            ])->first();

            $hasEnrolled = $enroll != null;
            $course->enroll = $enroll;
            $course->has_enrolled = $hasEnrolled;
        }

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
            'minimum_completing_modul' => 0,
            'minimum_correct_answer' => 0,
        ]);

        $cover->storeAs('public/cover_images', $coverFileName);

        $createBatch = Batch::create([
            'course_id' => $saveData->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'quantity' => $request->quantity,
            'start_quantity' => $request->quantity,
        ]);

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
            'minimum_correct_answer' => $request->minimum_correct_answer,
            'minimum_completing_modul' => $request->minimum_completing_modul,
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
        $course = Course::where('id', $id)->first();
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

            if ($ableToEnroll && $coupon->quantity <= 0) {
                $ableToEnroll = false;
            }
        }

        if ($ableToEnroll) {
            $c->decrement('quantity');

            $user = User::where('token', $request->token)->first();
            $now = Carbon::now()->format('Y-m-d');
            $b = Batch::where([
                ['course_id', $id],
                ['start_date', '<=', $now],
                ['end_date', '>=', $now],
            ]);
            $batch = $b->first();
            $b->decrement('quantity');

            $saveData = Enroll::create([
                'batch_id' => $batch->id,
                'coupon_id' => $coupon->id,
                'course_id' => $id,
                'user_id' => $user->id,
                'payment_status' => "PAID",
                'is_completed' => false,
                'has_answered_exam' => false,
            ]);

            // create presence date
            $presence_period = CarbonPeriod::create(
                Carbon::parse($now),
                Carbon::now()->addDays($course->presence_day_count)
            );

            foreach ($presence_period as $p => $period) {
                if ($p != count($presence_period) - 1) {
                    $savePeriod = Presence::create([
                        'enroll_id' => $saveData->id,
                        'user_id' => $user->id,
                        'presence_date' => $period->format('Y-m-d'),
                        'location' => null,
                        'checked_in' => $p == 0 ? true : false,
                    ]);
                }
            }

            // create path
            $moduls = Modul::where('course_id', $id)->orderBy('priority', 'DESC')->orderBy('updated_at', 'DESC')->get();
            foreach ($moduls as $mod) {
                $savePaths = EnrollPath::create([
                    'enroll_id' => $saveData->id,
                    'user_id' => $user->id,
                    'course_id' => $id,
                    'modul_id' => $mod->id,
                    'is_complete' => false,
                ]);
            }

            $message = "Berhasil enroll pelatihan";
            $status = 200;
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }
    public function completeEnroll($courseID, Request $request) {
        $data = Enroll::where('id', $request->enroll_id);
        $enroll = $data->with(['user', 'course.quiz'])->first();

        // Handle Presence
        $presences = Presence::where('enroll_id', $enroll->id)->update([
            'checked_in' => true,
        ]);

        // Handle Paths
        $paths = EnrollPath::where('enroll_id', $enroll->id)->update([
            'is_complete' => true,
        ]);
        
        // Handle Exam
        $answers = QuestionAnswer::where('quiz_id', $enroll->course->quiz->id)->update([
            'is_correct' => true,
        ]);

        return response()->json([
            'message' => "Berhasil meluluskan " . $enroll->user->name,
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
