<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // sementara dummy auth
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if ($request->email === env('MAIL_USERNAME') && $request->password === env('MAIL_PASSWORD')) {
            return response()->json([
                'status' => 'success',
                'token' => base64_encode($request->email . ":" . $request->password)
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
    }
}
