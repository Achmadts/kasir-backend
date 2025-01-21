<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Models\User;

class LoginController extends Controller
{
    public function index(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah!',
            ], 401);
        }

        if ($user->status === 'Inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
            ], 403);
        }

        try {
            $accessToken = JWTAuth::fromUser($user);
            $refreshToken = JWTAuth::fromUser($user);
            return response()->json([
                'success' => true,
                'token' => $accessToken,
                'message' => "Login berhasil",
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ])->cookie(
                    'refresh_token',
                    $refreshToken,
                    2592000, // 30 days in seconds
                    '/',
                    null,
                    true,
                    true
                );
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login gagal, coba lagi.',
            ], 500);
        }
    }

    public function refreshAccessToken(Request $request)
    {
        try {
            $refreshToken = $request->header('Authorization')
                ? str_replace('Bearer ', '', $request->header('Authorization'))
                : $request->cookie('refresh_token');

            if (!$refreshToken || !JWTAuth::setToken($refreshToken)->check()) {
                return response()->json(['message' => 'Refresh token invalid.'], 401);
            }

            $newAccessToken = JWTAuth::refresh($refreshToken);

            return response()->json([
                'success' => true,
                'token' => $newAccessToken,
                'message' => 'Token refreshed successfully'
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Refresh token has expired, please login again.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not refresh token.'], 500);
        }
    }

    public function logout()
    {
        try {
            $accessToken = JWTAuth::getToken();
            JWTAuth::invalidate($accessToken);

            $cookie = Cookie::forget('refresh_token');

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Logout'
            ])->withCookie($cookie);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout.'
            ], 500);
        }
    }

    public function getUser()
    {
        try {
            $user = auth()->guard('api')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => "You're Unauthenticated"
                ], 401);
            }

            return response()->json([
                'success' => true,
                'email' => $user->email,
                'user' => $user
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => "You're Unauthenticated"
            ], 401);
        }
    }
}