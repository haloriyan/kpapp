<?php

namespace App\Http\Controllers\Api;

use Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function auth(Request $request) {
        $user = User::where('token', $request->token)->first();

        return response()->json([
            'user' => $user,
        ]);
    }
    public function retrieve(Request $request) {
        $users = User::where('role', 'user')->orderBy('created_at', 'DESC')->paginate($request->limit);

        return response()->json([
            'users' => $users
        ]);
    }
    public function login(Request $request) {
        $message = "Kombinasi email dan password tidak tepat";
        $data = User::where('email', $request->email);
        $user = $data->first();
        $status = 401;

        if ($user != null) {
            if (Hash::check($request->password, $user->password)) {
                $token = Str::random(32);
                $data->update([
                    'token' => $token
                ]);
                $user = $data->first();
                $message = "Berhasil login.";
                $status = 200;
            }
        } else {
            $message = "Kami tidak dapat menemukan akun Anda";
        }

        return response()->json([
            'message' => $message,
            'user' => $user,
            'status' => $status,
        ]);
    }
    public function register(Request $request) {
        $token = Str::random(32);
        
        $saveData = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'user',
            'token' => $token,
        ]);

        return response()->json([
            'status' => 200,
            'user' => $saveData,
        ]);
    }
    public function logout(Request $request) {
        $data = User::where('token', $request->token);
        $user = $data->first();

        $data->update([
            'token' => null,
        ]);

        return response()->json(['message' => "ok"]);
    }
    public function update(Request $request) {
        $data = User::where('token', $request->token);
        $updateData = $data->update([
            'name' => $request->name,
        ]);
        $user = $data->first();

        return response()->json([
            'message' => "Berhasil memperbarui profil",
            'user' => $user,
        ]);
    }
}
