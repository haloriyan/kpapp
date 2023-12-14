<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use FFMpeg\FFMpeg;
use FFMpeg\Media as FFMpegMedia;
// use \ProtoneMedia\LaravelFFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function getByCourse($courseID) {
        $materials = Material::where('course_id', $courseID)->orderBy('priority', 'DESC')->orderBy('updated_at', 'ASC')->get();
        $course = Course::where('id', $courseID)->first();

        return response()->json([
            'materials' => $materials,
            'course' => $course,
        ]);
    }
    public function castSpace($path) {
        // $p = explode(" ", $path);
        // return implode("\ ", $p);
        return $path;
    }
    public function generateThumbnail($path, $filename) {
        if (file_exists($path)) {
            if (!in_array('thumbs', Storage::disk('public')->directories())) {
                Storage::disk('public')->makeDirectory('thumbs');
            }
            
            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open($path);
            $video->frame(
                \FFMpeg\Coordinate\TimeCode::fromSeconds(1)
            )->save(
                storage_path('app/public/thumbs/' . $filename . ".jpg"),
            );

            return true;
        } else {
            return false;
        }
    }
    public function store($courseID, Request $request) {
        $video = $request->file('video');
        $videoFileName = $video->getClientOriginalName();
        $video->storeAs('public/video_materials', $videoFileName);

        $ffmpeg = FFMpeg::create();
        $duration = $ffmpeg->open(
            storage_path('app/public/video_materials/' . $videoFileName), $videoFileName
        )->getStreams()->videos()->first()->get('duration');

        // Generating thumbnail
        $thumbnail = $this->generateThumbnail(
            storage_path('app/public/video_materials/' . $videoFileName), $videoFileName
        );

        $saveData = Material::create([
            'course_id' => $courseID,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => 0,
            'filename' => $videoFileName,
            'thumbnail' => $videoFileName . ".jpg",
            'duration' => $duration,
        ]);

        return response()->json([
            'message' => "Berhasil menambahkan materi video baru"
        ]);
    }
    public function delete(Request $request) {
        $data = Material::where('id', $request->id);
        $material = $data->first();

        $deleteData = $data->delete();
        $deleteVideo = Storage::delete('public/video_materials/' . $material->filename);

        return response()->json([
            'message' => "Berhasil menghapus materi " . $material->title,
        ]);
    }
}
