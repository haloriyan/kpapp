<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Pengajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengajarController extends Controller
{
    public function get($id, $pengajarID = NULL) {
        $course = Course::where('id', $id)->first();
        if ($pengajarID == NULL) {
            $pengajar = Pengajar::where('course_id', $id)->get();
        } else {
            $pengajar = Pengajar::where('id', $pengajarID)->first();
        }

        return response()->json([
            'pengajar' => $pengajar,
            'course' => $course,
        ]);
    }
    public function create($id, Request $request) {
        $photo = $request->file('photo');
        $photoFileName = rand(1111,9999)."_".$photo->getClientOriginalName();

        $saveData = Pengajar::create([
            'course_id' => $id,
            'name' => $request->name,
            'description' => $request->description,
            'photo' => $photoFileName
        ]);

        $photo->storeAs('public/pengajar_photos', $photoFileName);

        return response()->json([
            'message' => "Berhasil menambahkan data pengajar"
        ]);
    }
    public function update($id, Request $request) {
        $data = Pengajar::where('id', $request->id);
        $pengajar = $data->first();

        $toUpdate = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoFileName = rand(1111,9999)."_".$photo->getClientOriginalName();
            $toUpdate['photo'] = $photoFileName;
            $deleteOldPhoto = Storage::delete('public/pengajar_photos/' . $pengajar->photo);
            $photo->storeAs('public/pengajar_photos', $photoFileName);
        }

        $updateData = $data->update($toUpdate);

        return response()->json([
            'message' => "Berhasil mengubah data pengajar " . $pengajar->name,
        ]);
    }
    public function delete($id, Request $request) {
        $data = Pengajar::where('id', $request->id);
        $pengajar = $data->first();

        $deleteData = $data->delete();
        $deletePhoto = Storage::delete('public/pengajar_photos/' . $pengajar->photo);

        return response()->json([
            'message' => "Berhasil menghapus data pengajar"
        ]);
    }
}
