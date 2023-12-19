<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\Material;
use App\Models\Path;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    public function home(Request $request) {
        $categories = Category::orderBy('priority', 'DESC')->orderBy('updated_at', 'DESC')->get();
        $courses = Course::orderBy('created_at', 'DESC')->with(['materials'])->take(8)->get();

        return response()->json([
            'status' => 200,
            'categories' => $categories,
            'courses' => $courses,
        ]);
    }
    public function category(Request $request) {
        $categories = Category::orderBy('priority', 'DESC')->orderBy('updated_at', 'DESC')->get();
        $category = Category::where('id', $request->id)
        ->orWhere('name', 'LIKE', '%'.$request->id.'%')
        ->first();
        $courses = [];
        
        if ($category != null) {
            $courses = Course::where('category', 'LIKE', '%'.$category->name.'%')->get();
        }

        return response()->json([
            'status' => 200,
            'categories' => $categories,
            'category' => $category,
            'courses' => $courses,
        ]);
    }
    public function myCourse(Request $request) {
        $user = User::where('token', $request->token)->first();
        $enrolls = Enroll::where([
            ['user_id', $user->id],
            ['payment_status', 'PAID'],
        ])
        ->with(['course.materials', 'course.quiz', 'paths'])
        ->get();

        return response()->json([
            'enrolls' => $enrolls,
        ]);
    }
    public function learn(Request $request) {
        $e = Enroll::where('id', $request->enroll_id);
        $enroll = $e->with(['user', 'course.materials', 'paths'])
        ->first();

        // check paths
        if ($request->hit_path) {
            $checkPath = Path::where([
                ['user_id', $enroll->user_id],
                ['course_id', $enroll->course_id],
                ['enroll_id', $enroll->id],
                ['material_id', $request->material_id],
            ])->first();

            if ($checkPath == null) {
                $hittingPath = Path::create([
                    'user_id' => $enroll->user_id,
                    'course_id' => $enroll->course_id,
                    'enroll_id' => $enroll->id,
                    'material_id' => $request->material_id,
                ]);
            }

            $paths = Path::where('enroll_id', $enroll->id)->get();
            if ($enroll->course->materials->count() == $paths->count()) {
                $e->update([
                    'is_completed' => true,
                ]);
            }
        }

        return response()->json([
            'enroll' => $enroll,
        ]);
    }
    public function stream($materialID) {
        $material = Material::where('id', $materialID)->first();
        $videoPath = public_path('storage/video_materials/' . $material->filename);

        $stream = new \App\Http\VideoStream($videoPath);
        return response()->stream(function () use ($stream) {
            $stream->start();
        });
    }
    public function adminDashboard() {
        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $enroll_count = Enroll::whereBetween('created_at', [$startDate, $endDate])->get('id')->count();

        return response()->json([
            'enroll_count' => $enroll_count,
        ]);
    }
    public function search(Request $request) {
        $courses = Course::where([
            ['title', 'LIKE', '%'.$request->q.'%']
        ])
        ->with(['materials'])
        ->paginate(25);

        return response()->json([
            'courses' => $courses,
        ]);
    }
}
