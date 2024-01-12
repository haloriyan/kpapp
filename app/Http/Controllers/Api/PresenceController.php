<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function check(Request $request) {
        $data = Presence::where('id', $request->id);
        $data->update([
            'checked_in' => true,
        ]);

        return response()->json([
            'message' => "ok"
        ]);
    }
}
