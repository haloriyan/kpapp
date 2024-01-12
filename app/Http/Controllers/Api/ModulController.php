<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enroll;
use App\Models\Modul;
use App\Models\ModulDocument;
use App\Models\ModulVideo;
use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModulController extends Controller
{
    public function get($id, Request $request) {
        $course = Course::where('id', $id)->first();
        $moduls = Modul::where('course_id', $id)
        ->orderBy('priority', 'ASC')->orderBy('updated_at', 'DESC')
        ->with(['videos', 'documents'])
        ->get();

        return response()->json([
            'course' => $course,
            'moduls' => $moduls,
        ]);
    }
    public function create($id, Request $request) {
        $saveData = Modul::create([
            'course_id' => $id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => 0,
        ]);

        return response()->json([
            'message' => "Berhasil menambahkan modul"
        ]);
    }
    public function priority($id, $modulID, $action) {
        $data = Modul::where('id', $modulID);
        $modul = $data->first();

        if ($action == "decrease" && $modul->priority > 0) {
            $data->decrement('priority');
        } else {
            $data->increment('priority');
        }

        return response()->json([
            'message' => "ok"
        ]);
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
    public function storeVideo($id, $modulID, Request $request) {
        $video = $request->file('video');
        $videoFileName = $video->getClientOriginalName();
        $video->storeAs('public/video_materials', $videoFileName);

        $ffmpeg = FFMpeg::create();
        $duration = $ffmpeg->open(
            storage_path('app/public/video_materials/' . $videoFileName), $videoFileName
        )->getStreams()->videos()->first()->get('duration');

        $this->generateThumbnail(
            storage_path('app/public/video_materials/' . $videoFileName), $videoFileName
        );

        $saveData = ModulVideo::create([
            'modul_id' => $modulID,
            'title' => explode(".", $videoFileName)[0], 
            'thumbnail' => $videoFileName . '.jpg',
            'filename' => $videoFileName,
            'duration' => $duration,
            'priority' => 0,
        ]);

        return response()->json([
            'message' => "ok"
        ]);
    }
    public function deleteVideo($id, $modulID, Request $request) {
        $data = ModulVideo::where('id', $request->id);
        $video = $data->first();

        $deleteData = $data->delete();
        $deleteThumbnail = Storage::delete('public/thumbs/' . $video->thumbnail);
        $deleteVideo = Storage::delete('public/video_materials/' . $video->filename);
        
        return response()->json([
            'message' => "ok"
        ]);
    }

    public function storeDocument($id, $modulID, Request $request) {
        $document = $request->file('document');
        $ogFileName = $document->getClientOriginalName();

        $titles = explode(".", $ogFileName);
        $titles = array_slice($titles, 0, -1);
        $title = implode(".", $titles);
        $docFileName = time()."_".$modulID."_".$ogFileName;

        $saveData = ModulDocument::create([
            'modul_id' => $modulID,
            'filename' => $docFileName,
            'title' => $title,
            'size' => $document->getSize() / (1024 * 1024),
            'priority' => 0,
            'type' => "pdf"
        ]);

        $document->storeAs('public/modul_documents', $docFileName);

        return response()->json([
            'message' => "ok"
        ]);
    }
    public function deleteDocument($id, $modulID, Request $request) {
        $data = ModulDocument::where('id', $request->id);
        $doc = $data->first();

        $deleteData = $data->delete();
        $deleteFile = Storage::delete('public/modul_documents/' . $doc->filename);

        return response()->json([
            'message' => "ok"
        ]);
    }

    public function setModulPosition($enrollID, Request $request) {
        $data = Enroll::where('id', $enrollID);
        if ($request->action == "next") {
            $data->increment('modul_position');
        } else {
            $data->decrement('modul_position');
        }

        return response()->json([
            'message' => "ok"
        ]);
    }
}
