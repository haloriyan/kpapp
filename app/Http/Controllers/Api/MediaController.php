<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function getByCourse($courseID) {
        $medias = Media::where('course_id', $courseID)->get();
        $course = Course::where('id', $courseID)->first();

        return response()->json([
            'medias' => $medias,
            'course' => $course
        ]);
    }
    public function store($courseID, Request $request) {
        $media = $request->file('media');
        $mediaFileName = $media->getClientOriginalName();
        $mediaType = $request->media_type;

        $saveData = Media::create([
            'course_id' => $courseID,
            'filename' => $mediaFileName,
            'type' => $mediaType,
        ]);

        $media->storeAs('public/medias', $mediaFileName);

        return response()->json([
            'message' => "Berhasil menambahkan media " . $mediaType
        ]);
    }
    public function delete(Request $request) {
        $data = Media::where('id', $request->id);
        $media = $data->first();

        $data->delete();
        Storage::delete('public/medias/' . $media->filename);

        return response()->json([
            'message' => "Berhasil menghapus media " . $media->type
        ]);
    }
}
