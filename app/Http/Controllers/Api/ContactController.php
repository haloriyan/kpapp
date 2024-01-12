<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function get() {
        $messages = Contact::orderBy('created_at', 'DESC')->paginate(25);

        return response()->json([
            'messages' => $messages,
        ]);
    }
    public function store(Request $request) {
        $saveData = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'body' => $request->body,
            'has_read' => false,
        ]);

        return response()->json([
            'message' => "Berhasil mengirim pesan. Kami akan menghubungi Anda segera"
        ]);
    }
    public function delete(Request $request) {
        $deleteData = Contact::where('id', $request->id)->delete();

        return response()->json([
            'message' => "Berhasil menghapus pesan."
        ]);
    }
}
