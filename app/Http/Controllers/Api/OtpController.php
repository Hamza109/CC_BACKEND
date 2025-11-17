<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        // Format message
        $message = "Your OTP is {$otp}. for e-Nyaya Sarathi APP .";

        // Generate the key
        $key = hash('sha512', trim($this->username) . trim($this->senderid) . trim($message) . trim($this->deptSecureKey));

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $this->smsUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'username' => $this->username,
            'password' => $this->password,
            'senderid' => $this->senderid,
            'content' => $message,
            'smsservicetype' => 'singlemsg',
            'mobileno' => $mobileNumber,
            'key' => $key,
            'templateid' => $this->templateId,
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Uncomment if proxy is needed
        // curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        // curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyPort);

        // Execute the request
        $curl_output = curl_exec($ch);

        // Check for cURL errors
        if ($curl_output === false) {
            $error = curl_error($ch);
            curl_close($ch);
            
            Log::error('OTP SMS cURL Error', [
                'mobile_number' => $mobileNumber,
                'error' => $error,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP: ' . $error,
            ], 500);
        }

        curl_close($ch);

        // Log the response
        Log::info('OTP SMS Response', [
            'mobile_number' => $mobileNumber,
            'response' => $curl_output,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully to ' . $mobileNumber,
            'mobile_number' => $mobileNumber,
            // Note: In production, you may want to remove 'otp' from response for security
            // Only include it in development/testing environments
            'otp' => $otp,
        ], 200);
    }
}

