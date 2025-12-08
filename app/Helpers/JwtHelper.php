<?php

namespace App\Helpers;

class JwtHelper
{
    /**
     * Verify JWT token and return payload
     */
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature
        $expectedSignature = hash_hmac('sha256', "$header.$payload", config('app.key'), true);
        $expectedSignature = base64_encode($expectedSignature);

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        // Decode payload
        $decodedPayload = json_decode(base64_decode($payload), true);

        if (!$decodedPayload) {
            return null;
        }

        // Check expiration
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            return null;
        }

        return $decodedPayload;
    }

    /**
     * Extract token from Authorization header
     */
    public static function extractToken($request): ?string
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

