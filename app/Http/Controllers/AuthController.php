<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        // ✅ Tes koneksi ke IMAP
        $imap = @imap_open(
            '{mx.kirimemail.com:993/imap/ssl}INBOX',
            $email,
            $password
        );

        if (!$imap) {
            return response()->json(['status' => 'error', 'message' => 'Invalid email or password'], 401);
        }

        imap_close($imap);

        // ✅ Custom claims (tanpa User model)
        $payload = JWTFactory::customClaims([
            'sub'      => $email,     // subject wajib
            'email'    => $email,
            'iat'      => time(),
            'exp'      => time() + 3600
        ])->make();

        $token = JWTAuth::encode($payload)->get();
        

        return response()->json([
            'status' => 'success',
            'token'  => $token,
        ]);
    }

    public function me()
    {
        $payload = JWTAuth::parseToken()->getPayload();
        return response()->json($payload->toArray());
    }
}
