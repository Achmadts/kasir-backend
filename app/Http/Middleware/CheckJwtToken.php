<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class CheckJwtToken
{
    public function handle($request, Closure $next)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => "You're Unauthenticated"], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token has expired, please login again.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => "You're Unauthenticated"], 401);
        }

        return $next($request);
    }
}