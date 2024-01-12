<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\EnrollPath;
use App\Models\Material;
use App\Models\MaterialVideo;
use App\Models\Modul;
use App\Models\ModulVideo;
use App\Models\Path;
use App\Models\Presence;
use App\Models\QuestionAnswer;
use App\Models\Thread;
use App\Models\ThreadVote;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
    public function enroll(Request $request) {
        $e = Enroll::where('id', $request->enroll_id);
        $enroll = $e->with([
            'user', 'batch',
            'course.moduls.videos',
            'course.moduls.documents',
            'course.quiz.questions'
        ])
        ->first();

        // presence
        $presences = Presence::where('enroll_id', $enroll->id)->orderBy('presence_date', 'ASC')->get();
        $paths = EnrollPath::where('enroll_id', $enroll->id)->orderBy('id', 'ASC')->get();
        $answers = QuestionAnswer::where([
            ['user_id', $enroll->user_id],
            ['quiz_id', $enroll->course->quiz->id]
        ])->get();

        return response()->json([
            'enroll' => $enroll,
            'presences' => $presences,
            'paths' => $paths,
            'answers' => $answers,
        ]);
    }
    public function learn(Request $request) {
        $enrollID = $request->enroll_id;
        $modulID = $request->modul_id;

        $modul = Modul::where('id', $modulID)->with(['videos', 'documents'])->first();

        return response()->json([
            'modul' => $modul
        ]);
    }
    public function doneLearn(Request $request) {
        $path = EnrollPath::where([
            ['modul_id', $request->modul_id],
            ['enroll_id', $request->enroll_id],
        ])->update([
            'is_complete' => true,
        ]);

        return response()->json(['ok']);
    }
    public function learns(Request $request) {
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
    public function stream($videoID) {
        $video = ModulVideo::where('id', $videoID)->first();
        $videoPath = public_path('storage/video_materials/' . $video->filename);

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
    public function forum($courseID, Request $request) {
        $course = Course::find($courseID);
        $user = User::where('token', $request->token)->first();
        $ableToPost = false;
        
        $threads = Thread::where('course_id', $course->id)
        ->with(['user'])
        ->orderBy('created_at', 'DESC')->paginate(25);

        if ($user != null) {
            $enrolls = Enroll::where([
                ['user_id', $user->id],
                ['course_id', $courseID]
            ])->get(['id']);

            if ($enrolls->count() > 0) {
                $ableToPost = true;
            }

            foreach ($threads as $t => $thread) {
                $threads[$t]->i_have_upvoted = false;
                $threads[$t]->i_have_downvoted = false;
    
                $baseFilter = [
                    ['user_id', $user->id],
                    ['thread_id', $thread->id]
                ];

                $threads[$t]->i_have_upvoted = ThreadVote::where([...$baseFilter, ['type', 'upvote']])->get(['id'])->count() > 0;
                $threads[$t]->i_have_downvoted = ThreadVote::where([...$baseFilter, ['type', 'downvote']])->get(['id'])->count() > 0;
            }
        }

        return response()->json([
            'course' => $course,
            'threads' => $threads,
            'able_to_post' => $ableToPost,
        ]);
    }
}
