<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Event;
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
}
