<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use App\Models\RefreshToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class OtpController extends Controller
{
    private $smsUrl = "https://msdgweb.mgov.gov.in/esms/sendsmsrequestDLT";
    private $username = "jkitd-jklms";
    private $password;
    private $senderid = "JKGOVT";
    private $deptSecureKey = "d8d11f7c-4f65-470c-999e-06595e1e53de";
    private $templateId = '1007901850983047344';
    private $proxy = "192.168.13.176";
    private $proxyPort = "54845";

    public function __construct()
    {
        // Calculate password hash exactly like the test PHP file
        $this->password = hash('sha1', "Lawaffair@789");
    }

    /**
     * @OA\Post(
     *   path="/api/otp/send",
     *   summary="Send OTP via SMS",
     *   description="Sends an OTP to the provided mobile number using the government SMS gateway",
     *   tags={"OTP"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"mobile_number"},
     *       @OA\Property(property="mobile_number", type="string", description="Mobile number (10 digits without country code, or 12 digits with country code 91). Example: 9419114719 or 919419114719", example="9419114719")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OTP sent successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="OTP sent successfully"),
     *       @OA\Property(property="otp", type="string", example="873453")
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="errors", type="object")
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="SMS sending failed",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="Failed to send OTP")
     *     )
     *   )
     * )
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $mobileNumber = trim($request->input('mobile_number'));
        
        // If mobile number doesn't start with country code (91 for India), add it
        if (strlen($mobileNumber) === 10) {
            $mobileNumber = '91' . $mobileNumber;
        }

        // Always generate a fresh 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP hash (NOT plaintext) - do this BEFORE sending SMS
        OtpVerification::createForMobile(
            $mobileNumber,
            $otp,
            $request->ip()
        );

        // Format message
        $message = "Your OTP is {$otp} 10 Cases will be heard on 16-11-2025. Check details on LMS Portal at https://jklms.jk.gov.in/u.php?i=hmdbranc49&d=HMD&o=14-11-2025 - JKGOVT
";

        // Generate the key
        $key = hash('sha512', trim($this->username) . trim($this->senderid) . trim($message) . trim($this->deptSecureKey));

        // Log credentials for debugging (remove in production)
        Log::debug('OTP SMS Request Details', [
            'username' => $this->username,
            'password_hash' => $this->password,
            'senderid' => $this->senderid,
            'mobile_number' => $mobileNumber,
            'message' => $message,
            'key' => $key,
        ]);

        // Initialize cURL
        $ch = curl_init();

        // Build POST data exactly like the test PHP file - using http_build_query
        $postData = http_build_query([
            'username' => $this->username,
            'password' => $this->password,
            'senderid' => $this->senderid,
            'content' => $message,
            'smsservicetype' => 'singlemsg',
            'mobileno' => $mobileNumber,
            'key' => $key,
            'templateid' => $this->templateId,
        ]);

        // Log the exact data being sent
        Log::debug('OTP SMS Request Data', [
            'post_data_string' => $postData,
        ]);

        // Set cURL options - exactly like the test PHP file
        curl_setopt($ch, CURLOPT_URL, $this->smsUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Enable verbose output for debugging (can be removed later)
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        // Uncomment if proxy is needed
        // curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        // curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyPort);

        // Execute the request
        $curl_output = curl_exec($ch);
        
        // Get verbose output
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        fclose($verbose);

        // Check for cURL errors
        if ($curl_output === false) {
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            Log::error('OTP SMS cURL Error', [
                'mobile_number' => $mobileNumber,
                'error' => $error,
                'http_code' => $httpCode,
                'verbose' => $verboseLog,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP: ' . $error,
            ], 500);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log the response with full details
        Log::info('OTP SMS Response', [
            'mobile_number' => $mobileNumber,
            'response' => $curl_output,
            'http_code' => $httpCode,
            'message' => $message,
            'key' => $key,
            'verbose' => $verboseLog,
        ]);

        // Check if response indicates hash mismatch error
        if (stripos($curl_output, 'Hash is not matching') !== false || stripos($curl_output, 'Error 416') !== false) {
            Log::error('OTP SMS Hash Mismatch', [
                'mobile_number' => $mobileNumber,
                'response' => $curl_output,
                'calculated_key' => $key,
                'message' => $message,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'SMS gateway authentication failed. Hash mismatch error.',
                'error_code' => 'HASH_MISMATCH',
            ], 500);
        }

        // Check for other error responses
        if (stripos($curl_output, 'Error') !== false && stripos($curl_output, '402') === false) {
            Log::error('OTP SMS Gateway Error', [
                'mobile_number' => $mobileNumber,
                'response' => $curl_output,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'SMS gateway returned an error: ' . $curl_output,
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully to ' . $mobileNumber,
            'mobile_number' => $mobileNumber,
            // DO NOT return OTP in production - it's stored as hash only
        ], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/otp/verify",
     *   summary="Verify OTP and get tokens",
     *   description="Verifies the OTP and issues JWT access token and refresh token",
     *   tags={"OTP"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"mobile_number", "otp"},
     *       @OA\Property(property="mobile_number", type="string", example="9419114719"),
     *       @OA\Property(property="otp", type="string", example="123456")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OTP verified, tokens issued",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="access_token", type="string"),
     *       @OA\Property(property="token_type", type="string", example="Bearer"),
     *       @OA\Property(property="expires_in", type="integer", example=3600)
     *     )
     *   ),
     *   @OA\Response(response=401, description="Invalid OTP"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $mobileNumber = trim($request->input('mobile_number'));
        if (strlen($mobileNumber) === 10) {
            $mobileNumber = '91' . $mobileNumber;
        }

        $otp = $request->input('otp');

        // Find valid OTP verification
        $otpVerification = OtpVerification::where('mobile_number', $mobileNumber)
            ->valid()
            ->latest()
            ->first();

        if (!$otpVerification || !$otpVerification->verify($otp)) {
            Log::warning('OTP verification failed', [
                'mobile_number' => $mobileNumber,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired OTP',
            ], 401);
        }

        // Generate JWT access token
        $accessToken = $this->generateAccessToken($mobileNumber);

        // Generate refresh token
        $refreshTokenData = RefreshToken::createForMobile(
            $mobileNumber,
            $request->ip(),
            $request->userAgent()
        );

        // Determine if we're in secure environment (HTTPS)
        $isSecure = request()->secure() || config('app.env') === 'production';
        
        // Create response with explicit JSON content type
        $response = response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully',
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 1 hour
        ], 200)->header('Content-Type', 'application/json');

        // Set refresh token as HttpOnly Secure cookie with SameSite protection
        $response->cookie(
            'refresh_token',
            $refreshTokenData['token'],
            60 * 24 * 30, // 30 days
            '/',
            null, // Domain - null means current domain
            $isSecure, // Secure (HTTPS only in production)
            true,  // HttpOnly - prevents JavaScript access (CRITICAL for security)
            false, // Raw - don't URL encode
            'Lax'  // SameSite - prevents CSRF attacks
        );

        return $response;
    }

    /**
     * @OA\Post(
     *   path="/api/otp/refresh",
     *   summary="Refresh access token",
     *   description="Refreshes the access token using the refresh token cookie",
     *   tags={"OTP"},
     *   @OA\Response(
     *     response=200,
     *     description="Token refreshed",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="access_token", type="string"),
     *       @OA\Property(property="token_type", type="string", example="Bearer"),
     *       @OA\Property(property="expires_in", type="integer", example=3600)
     *     )
     *   ),
     *   @OA\Response(response=401, description="Invalid refresh token")
     * )
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Refresh token not found',
            ], 401);
        }

        $tokenHash = hash('sha256', $refreshToken);
        $refreshTokenModel = RefreshToken::where('token_hash', $tokenHash)
            ->valid()
            ->first();

        if (!$refreshTokenModel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired refresh token',
            ], 401);
        }

        // Update last used
        $refreshTokenModel->update(['last_used_at' => now()]);

        // Generate new access token
        $accessToken = $this->generateAccessToken($refreshTokenModel->mobile_number);

        return response()->json([
            'status' => 'success',
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 200);
    }

    /**
     * Generate JWT access token
     */
    private function generateAccessToken(string $mobileNumber): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode([
            'sub' => $mobileNumber,
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour
            'type' => 'access',
        ]));
        
        $signature = hash_hmac('sha256', "$header.$payload", config('app.key'), true);
        $signature = base64_encode($signature);
        
        return "$header.$payload.$signature";
    }
}

