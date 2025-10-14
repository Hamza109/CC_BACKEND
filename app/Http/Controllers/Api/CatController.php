<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenApi\Annotations as OA;

class CatController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/cat/casedetails",
     *   summary="Get CAT case details by case number",
     *   tags={"CAT"},
     *   @OA\Parameter(
     *     name="caseNo",
     *     in="query",
     *     description="Case number",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="caseType",
     *     in="query",
     *     description="Case type (e.g., OA)",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="caseYear",
     *     in="query",
     *     description="Case year (e.g., 2024)",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="location",
     *     in="query",
     *     description="Location code",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful response",
     *     @OA\JsonContent(
     *       oneOf={
     *         @OA\Schema(
     *           type="object",
     *           @OA\Property(property="status", type="string", example="success"),
     *           @OA\Property(property="message", type="object")
     *         ),
     *         @OA\Schema(
     *           type="object",
     *           @OA\Property(property="status", type="string", example="error"),
     *           @OA\Property(property="error", type="string", example="Invalid API response or no data found")
     *         )
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad request"
     *   )
     * )
     */
    public function caseDetails(Request $request)
    {
        // Accept either camelCase (caseNo, caseType, caseYear, location) or snake_case
        $caseNo = trim((string) ($request->query('caseNo') ?? $request->query('case_no') ?? ''));
        $caseType = trim((string) ($request->query('caseType') ?? $request->query('case_type') ?? ''));
        $caseYear = trim((string) ($request->query('caseYear') ?? $request->query('case_year') ?? ''));
        $location = trim((string) ($request->query('location') ?? $request->query('location_code') ?? ''));

        if (!$caseNo || !$caseType || !$caseYear || !$location) {
            return response()->json(['status' => 'error', 'error' => 'Missing required parameters']);
        }

        $apiUrl = 'https://cgat.gov.in/api/index.php/cat/v1/casedetailcaseno'
            . '?case_no=' . urlencode($caseNo)
            . '&case_year=' . urlencode($caseYear)
            . '&case_type=' . urlencode($caseType)
            . '&location_code=' . urlencode($location);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
            CURLOPT_USERPWD => 'admin:1234',
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $curlResponse = curl_exec($ch);
        if ($curlResponse === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return response()->json(['status' => 'error', 'error' => 'Curl error: ' . $error]);
        }

        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($curlResponse, true);
        if ($httpStatus >= 400) {
            return response()->json(['status' => 'error', 'error' => 'CAT API ' . $httpStatus . ': ' . $curlResponse], $httpStatus);
        }

        if (is_array($data) && isset($data['status']) && $data['status'] === 'success') {
            return response()->json(['status' => 'success', 'message' => $data['message']]);
        }

        return response()->json(['status' => 'error', 'error' => 'Invalid API response or no data found']);
    }
}
