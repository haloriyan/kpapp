<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function get($id) {
        $course = Course::where('id', $id)->first();
        $events = Event::where('course_id', $id)->orderBy('start_date', 'ASC')->get();

        return response()->json([
            'course' => $course,
            'events' => $events
        ]);
    }
    public function getByID($courseID, $eventID, Request $request) {
        $query = Event::where('id', $eventID);
        if ($request->with != "") {
            $query = $query->with($request->with);
        }
        $event = $query->first();

        return response()->json([
            'event' => $event,
        ]);
    }
    public function create(Request $request) {
        $cover = $request->file('cover');
        $coverFileName = $cover->getClientOriginalName();

        $course = Course::where('id', $request->course_id)->first();

        $saveData = Event::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'cover' => $coverFileName,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'stream_url' => $request->stream_url,
            'join_rule' => $request->join_rule,
        ]);

        $cover->storeAs('public/event_covers', $coverFileName);

        return response()->json([
            'message' => "Berhasil membuat event baru untuk pelatihan " . $course->title,
        ]);
    }
    public function update(Request $request) {
        $data = Event::where('id', $request->id);
        $event = $data->first();

        $toUpdate = [
            'title' => $request->title,
            'description' => $request->description,
            'stream_url' => $request->stream_url,
            'join_rule' => $request->join_rule,
        ];

        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $coverFileName = $cover->getClientOriginalName();
            $deleteOldCover = Storage::delete('public/event_covers/' . $event->cover);
            $toUpdate['cover'] = $coverFileName;
            $cover->storeAs('public/event_covers', $coverFileName);
        }

        $updateData = $data->update($toUpdate);

        return response()->json([
            'message' => "Berhasil mengubah data event " . $event->title,
        ]);
    }
    public function delete(Request $request) {
        $data = Event::where('id', $request->id);
        $event = $data->first();

        $deleteData = $data->delete();
        $deleteCover = Storage::delete('public/event_covers/' . $event->cover);

        return response()->json([
            'message' => "Berhasil menghapus event " . $event->title,
        ]);
    }
    public function join(Request $request) {
        $user = User::where('token', $request->token)->first();
        $event = Event::where('id', $request->event_id)->first();

        $data = EventUser::where([
            ['event_id', $request->event_id],
            ['user_id', $user->id]
        ])->get(['id']);

        if ($data->count() == 0) {
            $enrollID = null;
            $enroll = Enroll::where([
                ['user_id', $user->id],
                ['course_id', $event->course_id]
            ])->first(['id']);
            if ($enroll != null) {
                $enrollID = $enroll->id;
            }

            $saveData = EventUser::create([
                'user_id' => $user->id,
                'event_id' => $request->event_id,
                'enroll_id' => $enrollID,
            ]);
        }

        return response()->json([
            'status' => 200,
        ]);
    }
}
