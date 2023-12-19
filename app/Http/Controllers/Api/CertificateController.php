<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function get($id) {
        $course = Course::where('id', $id)->first();
        $certificate = Certificate::where('course_id', $id)->first();
        
        return response()->json([
            'course' => $course,
            'certificate' => $certificate,
        ]);
    }
    public function put(Request $request) {
        $data = Certificate::where('course_id', $request->course_id);
        $certificate = $data->first();
        $newData = true;

        if ($certificate != null) {
            $deleteFile = Storage::delete('public/certificate_templates/' . $certificate->filename);
            $deleteData = $data->delete();
        }

        self::store($request, $certificate);
        
        return response()->json([
            'message' => "Berhasil membuat sertifikat"
        ]);
    }
    public static function store($request, $certificate = null) {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        if ($certificate != null) {
            $saveData = Certificate::create([
                'course_id' => $request->course_id,
                'filename' => $fileName,
                'font_properties' => json_encode([
                    'fontSize' => '50',
                    'fontWeight' => '700',
                    'fontFamily' => "Times"
                ]),
                'position' => '40'
            ]);
        } else {
            $updateData = Certificate::where('id', $certificate->id)->update([
                'filename' => $fileName
            ]);
        }

        $file->storeAs('public/certificate_templates', $fileName);

        return $saveData;
    }
    public function update($id, Request $request) {
        $data = Certificate::where('course_id', $id);
        $certificate = $data->first();

        $toUpdate = [
            'font_properties' => $request->font_properties,
            'position' => $request->position
        ];

        $updateData = $data->update($toUpdate);

        return response()->json([
            'message' => "Berhasil mengubah untuk " . $certificate->course->title,
        ]);
    }
}
