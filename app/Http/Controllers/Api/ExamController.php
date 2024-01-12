<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\Question;
use App\Models\QuestionAnswer as Answer;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    public function getQuestions($id) {
        $course = Course::where('id', $id)->with(['quiz.questions'])->first();
        $answers_count = Answer::where('quiz_id', $course->id)->get('id')->count();

        return response()->json([
            'course' => $course,
            'answers_count' => $answers_count,
        ]);
    }
    public function storeQuestion($id, Request $request) {
        if ($request->create_quiz == "1") {
            $quiz = Quiz::create([
                'course_id' => $id,
                'title' => "Uji Kompetensi",
                'visibility' => 'after_completion'
            ]);
        } else {
            $quiz = Quiz::where('course_id', $id)->first();
        }
        
        $toSave = [
            'quiz_id' => $quiz->id,
            'type' => $request->type,
            'body' => $request->body,
            'options' => json_encode(explode(",", $request->options)),
            'expected_answer' => $request->expected_answer,
        ];

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageFileName = date('YmdHis')."_".rand(111111, 999999)."_".$image->getClientOriginalName();
            $image->storeAs('public/question_images', $imageFileName);
            $toSave['image'] = $imageFileName;
        }

        $saveData = Question::create($toSave);

        return response()->json([
            'message' => "Berhasil menambahkan pertanyaan"
        ]);
    }
    public function deleteQuestion($id, Request $request) {
        $data = Question::where('id', $request->id);
        $question = $data->first();

        $data->delete();
        if ($question->image != null) {
            $deleteImage = Storage::delete('public/question_images/' . $question->image);
        }

        return response()->json([
            'message' => "Berhasil menghapus pertanyaan"
        ]);
    }
    public function submitAnswer(Request $request) {
        $answers = $request->answers;
        $e = Enroll::where('id', $request->enroll_id);
        $enroll = $e->first();
        
        foreach ($answers as $a => $answer) {
            $question = Question::where('id', $answer['question_id'])->first();
            $isCorrect = $question->expected_answer == $answer['answer'];
            $answers[$a]['is_correct'] = $isCorrect;
        }

        foreach ($answers as $answer) {
            $saveAnswer = Answer::create($answer);
        }

        $e->update(['has_answered_exam' => true]);

        return response()->json([
            'message' => "ok"
        ]);
    }
    public function syncCounter($courseID, Request $request) {
        $c = Course::where('id', $courseID)->update([
            'minimum_correct_answer' => $request->count,
        ]);

        return response()->json(['ok']);
    }
    public function answer($courseID) {
        $course = Course::where('id', $courseID)
        ->with(['enrolls.user', 'quiz.questions'])
        ->first();

        $answersRaw = Answer::where('quiz_id', $course->quiz->id)->get();

        $users = [];
        foreach ($course->enrolls as $enroll) {
            $u = $enroll->user;
            $uAnswers = [];
            foreach ($answersRaw as $ans) {
                if ($ans->user_id == $u->id) {
                    array_push($uAnswers, $ans);
                }
            }
            $u->answers = $uAnswers;
            array_push($users, $u);
        }

        return response()->json([
            'course' => $course,
            'users' => $users,
        ]);
    }
}
