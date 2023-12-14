<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    public function home(Request $request) {
        $categories = Category::orderBy('priority', 'DESC')->orderBy('updated_at', 'DESC')->get();
        $courses = Course::orderBy('created_at', 'DESC')->take(8)->get();

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
        ->with(['course.materials'])
        ->get();

        return response()->json([
            'enrolls' => $enrolls,
        ]);
    }
    public function learn(Request $request) {
        $enroll = Enroll::where('id', $request->enroll_id)
        ->with(['user', 'course.materials'])
        ->first();

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
}
