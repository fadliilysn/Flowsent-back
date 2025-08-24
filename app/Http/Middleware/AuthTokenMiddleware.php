<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7); // ambil token setelah "Bearer "

        try {
            $decoded = base64_decode($token);
            [$email, $password] = explode(':', $decoded);

            // validasi sederhana: email & password wajib ada
            if (empty($email) || empty($password)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid token'], 401);
            }

            // simpan credential ke request supaya bisa dipakai di controller
            $request->merge([
                'auth_email' => $email,
                'auth_password' => $password,
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid token format'], 401);
        }

        return $next($request);
    }
}
