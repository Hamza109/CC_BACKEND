<?php

namespace App\Http\Middleware;

use App\Helpers\JwtHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyJwtToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = JwtHelper::extractToken($request);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authorization token not provided',
            ], 401);
        }

        $payload = JwtHelper::verify($token);

        if (!$payload) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token',
            ], 401);
        }

        // Check if token is access token (not refresh token)
        if (isset($payload['type']) && $payload['type'] !== 'access') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token type',
            ], 401);
        }

        // Attach user info to request
        $request->merge([
            'auth_mobile_number' => $payload['sub'] ?? null,
            'auth_payload' => $payload,
        ]);

        return $next($request);
    }
}
