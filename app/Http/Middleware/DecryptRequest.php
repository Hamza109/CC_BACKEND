<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class DecryptRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only decrypt POST requests with encrypted payload
        if ($request->isMethod('post') && $request->has('encrypted')) {
            $encryptedData = $request->input('encrypted');
            
            // Check if encrypted data is in the correct format (contains colon separator)
            // Format should be: "iv:encryptedData"
            if (is_string($encryptedData) && strpos($encryptedData, ':') !== false && strlen($encryptedData) > 20) {
                try {
                    // Decrypt the payload
                    $decryptedData = $this->decrypt($encryptedData);
                    
                    // Replace request data with decrypted data
                    $request->merge($decryptedData);
                    
                    // Remove the encrypted field
                    $request->request->remove('encrypted');
                    
                } catch (Exception $e) {
                    Log::warning('Decryption failed, allowing request to proceed without decryption', [
                        'error' => $e->getMessage(),
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'encrypted_length' => strlen($encryptedData),
                    ]);
                    
                    // If decryption fails, remove the encrypted field and allow request to proceed
                    // This handles cases where:
                    // 1. Frontend sent encrypted data but key doesn't match
                    // 2. Frontend sent malformed encrypted data
                    // 3. Request might have other fields that should be processed
                    $request->request->remove('encrypted');
                    
                    // Don't block the request - let the controller handle validation
                    // If the request is truly invalid, the controller will return appropriate error
                }
            } else {
                // If encrypted field exists but doesn't have proper format,
                // it might be a false positive - remove it and continue
                $request->request->remove('encrypted');
            }
        }
        
        return $next($request);
    }
    
    /**
     * Decrypt AES-256-CBC encrypted data
     *
     * @param string $encryptedData Format: "iv:encryptedData" (both base64)
     * @return array Decrypted data as array
     * @throws Exception
     */
    private function decrypt(string $encryptedData): array
    {
        // Split IV and encrypted data
        $parts = explode(':', $encryptedData);
        
        if (count($parts) !== 2) {
            throw new Exception('Invalid encrypted data format. Expected "iv:encryptedData"');
        }
        
        [$ivBase64, $encryptedBase64] = $parts;
        
        // Decode from Base64
        $iv = base64_decode($ivBase64, true);
        $encrypted = base64_decode($encryptedBase64, true);
        
        if ($iv === false || $encrypted === false) {
            throw new Exception('Invalid Base64 encoding');
        }
        
        // Validate IV size (must be 16 bytes for AES-256-CBC)
        if (strlen($iv) !== 16) {
            throw new Exception('Invalid IV size. Expected 16 bytes');
        }
        
        // Get encryption key from config
        $key = config('app.encryption_key');
        
        if (empty($key)) {
            throw new Exception('Encryption key not configured. Set ENCRYPTION_KEY in .env');
        }
        
        // Ensure key is 32 bytes (256 bits) for AES-256
        // If key is shorter, hash it; if longer, truncate
        if (strlen($key) !== 32) {
            $key = substr(hash('sha256', $key), 0, 32);
        }
        
        // Decrypt using AES-256-CBC
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($decrypted === false) {
            $error = openssl_error_string();
            throw new Exception('Decryption failed: ' . ($error ?: 'Unknown OpenSSL error'));
        }
        
        // Decode JSON
        $data = json_decode($decrypted, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON in decrypted data: ' . json_last_error_msg());
        }
        
        return $data;
    }
}

