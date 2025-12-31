<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class CatController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/cat/case-details",
     *   summary="Get CAT case details by case number",
     *   tags={"CAT"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"catschemaId", "casetypeId", "caseNo", "caseYear"},
     *       @OA\Property(property="catschemaId", type="string", description="Location code (e.g., 117 for Jammu, 119 for Srinagar)", example="117"),
     *       @OA\Property(property="casetypeId", type="string", description="Case type ID (e.g., 1 for Original Application)", example="1"),
     *       @OA\Property(property="caseNo", type="string", description="Case number", example="123"),
     *       @OA\Property(property="caseYear", type="string", description="Case year", example="2024")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful response",
     *     @OA\JsonContent(
     *       oneOf={
     *         @OA\Schema(
     *           type="object",
     *           @OA\Property(property="status", type="string", example="success"),
     *           @OA\Property(property="data", type="object")
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
        $catschemaId = trim((string) ($request->input('catschemaId') ?? ''));
        $casetypeId = trim((string) ($request->input('casetypeId') ?? ''));
        $caseNo = trim((string) ($request->input('caseNo') ?? ''));
        $caseYear = trim((string) ($request->input('caseYear') ?? ''));

        if (!$catschemaId || !$casetypeId || !$caseNo || !$caseYear) {
            return response()->json([
                'status' => 'error',
                'error' => 'Missing required parameters: catschemaId, casetypeId, caseNo, caseYear'
            ], 400);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://cgat.gov.in/catapi/casedetail_individual_case_no_wise11',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'catschemaId' => $catschemaId,
                'casetypeId' => $casetypeId,
                'caseNo' => $caseNo,
                'caseYear' => $caseYear
            ],
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return response()->json([
                'status' => 'error',
                'error' => 'Curl error: ' . $error
            ], 500);
        }

        curl_close($ch);

        if ($httpStatus >= 400) {
            return response()->json([
                'status' => 'error',
                'error' => 'CAT API returned status ' . $httpStatus . ': ' . $response
            ], $httpStatus);
        }

        $data = json_decode($response, true);
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'error' => 'Invalid JSON response from CAT API'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/cat/daily-orders",
     *   summary="Get CAT daily orders by case number",
     *   tags={"CAT"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"catschemaId", "casetypeId", "caseNo", "caseYear"},
     *       @OA\Property(property="catschemaId", type="string", description="Location code (e.g., 117 for Jammu, 119 for Srinagar)", example="117"),
     *       @OA\Property(property="casetypeId", type="string", description="Case type ID (e.g., 1 for Original Application)", example="1"),
     *       @OA\Property(property="caseNo", type="string", description="Case number", example="123"),
     *       @OA\Property(property="caseYear", type="string", description="Case year", example="2024")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful response",
     *     @OA\JsonContent(
     *       oneOf={
     *         @OA\Schema(
     *           type="object",
     *           @OA\Property(property="status", type="string", example="success"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object"))
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
    public function dailyOrders(Request $request)
    {
        $catschemaId = trim((string) ($request->input('catschemaId') ?? ''));
        $casetypeId = trim((string) ($request->input('casetypeId') ?? ''));
        $caseNo = trim((string) ($request->input('caseNo') ?? ''));
        $caseYear = trim((string) ($request->input('caseYear') ?? ''));

        if (!$catschemaId || !$casetypeId || !$caseNo || !$caseYear) {
            return response()->json([
                'status' => 'error',
                'error' => 'Missing required parameters: catschemaId, casetypeId, caseNo, caseYear'
            ], 400);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://cgat.gov.in/catapi/getCatDailyOrderReportCaseNo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'catschemaId' => $catschemaId,
                'caseYear' => $caseYear,
                'caseType' => $casetypeId,
                'caseNo' => $caseNo
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return response()->json([
                'status' => 'error',
                'error' => 'Curl error: ' . $error
            ], 500);
        }

        curl_close($ch);

        if ($httpStatus >= 400) {
            return response()->json([
                'status' => 'error',
                'error' => 'CAT API returned status ' . $httpStatus . ': ' . $response
            ], $httpStatus);
        }

        $data = json_decode($response, true);
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'error' => 'Invalid JSON response from CAT API'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => is_array($data) ? $data : []
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/cat/final-orders",
     *   summary="Get CAT final orders by case number",
     *   tags={"CAT"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"catschemaId", "casetypeId", "caseNo", "caseYear"},
     *       @OA\Property(property="catschemaId", type="string", description="Location code (e.g., 117 for Jammu, 119 for Srinagar)", example="117"),
     *       @OA\Property(property="casetypeId", type="string", description="Case type ID (e.g., 1 for Original Application)", example="1"),
     *       @OA\Property(property="caseNo", type="string", description="Case number", example="123"),
     *       @OA\Property(property="caseYear", type="string", description="Case year", example="2024")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful response",
     *     @OA\JsonContent(
     *       oneOf={
     *         @OA\Schema(
     *           type="object",
     *           @OA\Property(property="status", type="string", example="success"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object"))
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
    public function finalOrders(Request $request)
    {
        $catschemaId = trim((string) ($request->input('catschemaId') ?? ''));
        $casetypeId = trim((string) ($request->input('casetypeId') ?? ''));
        $caseNo = trim((string) ($request->input('caseNo') ?? ''));
        $caseYear = trim((string) ($request->input('caseYear') ?? ''));

        if (!$catschemaId || !$casetypeId || !$caseNo || !$caseYear) {
            return response()->json([
                'status' => 'error',
                'error' => 'Missing required parameters: catschemaId, casetypeId, caseNo, caseYear'
            ], 400);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://cgat.gov.in/catapi/getCatFinalOrderReportCaseNo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'catschemaId' => $catschemaId,
                'caseYear' => $caseYear,
                'caseType' => $casetypeId,
                'caseNo' => $caseNo
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return response()->json([
                'status' => 'error',
                'error' => 'Curl error: ' . $error
            ], 500);
        }

        curl_close($ch);

        if ($httpStatus >= 400) {
            return response()->json([
                'status' => 'error',
                'error' => 'CAT API returned status ' . $httpStatus . ': ' . $response
            ], $httpStatus);
        }

        $data = json_decode($response, true);
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'error' => 'Invalid JSON response from CAT API'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => is_array($data) ? $data : []
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/cat/cases/search",
     *   summary="Search cases",
     *   description="Searches the cases table using optional filters.",
     *   tags={"CAT"},
     *   @OA\Parameter(
     *     name="applicant",
     *     in="query",
     *     required=false,
     *     description="Filter by applicant name (partial match)",
     *     @OA\Schema(type="string", example="John Doe")
     *   ),
     *   @OA\Parameter(
     *     name="respondent",
     *     in="query",
     *     required=false,
     *     description="Filter by respondent name (partial match)",
     *     @OA\Schema(type="string", example="State")
     *   ),
     *   @OA\Parameter(
     *     name="applicantadvocate1",
     *     in="query",
     *     required=false,
     *     description="Filter by applicant advocate 1 (partial match)",
     *     @OA\Schema(type="string", example="Adv. Sharma")
     *   ),
     *   @OA\Parameter(
     *     name="applicantadvocate",
     *     in="query",
     *     required=false,
     *     description="Filter by applicant advocate (partial match)",
     *     @OA\Schema(type="string", example="Adv. Kumar")
     *   ),
     *   @OA\Parameter(
     *     name="respondentadvocate",
     *     in="query",
     *     required=false,
     *     description="Filter by respondent advocate (partial match)",
     *     @OA\Schema(type="string", example="Adv. Singh")
     *   ),
     *   @OA\Parameter(
     *     name="location",
     *     in="query",
     *     required=false,
     *     description="Filter by location (partial match)",
     *     @OA\Schema(type="string", example="Jammu")
     *   ),
     *   @OA\Parameter(
     *     name="case_type",
     *     in="query",
     *     required=false,
     *     description="Filter by case type (partial match)",
     *     @OA\Schema(type="string", example="Civil")
     *   ),
     *   @OA\Parameter(
     *     name="case_no",
     *     in="query",
     *     required=false,
     *     description="Filter by case number (exact match)",
     *     @OA\Schema(type="string", example="123")
     *   ),
     *   @OA\Parameter(
     *     name="year",
     *     in="query",
     *     required=false,
     *     description="Filter by year (exact match)",
     *     @OA\Schema(type="integer", example=2024)
     *   ),
     *   @OA\Parameter(
     *     name="caseno",
     *     in="query",
     *     required=false,
     *     description="Filter by caseno (exact match)",
     *     @OA\Schema(type="string", example="123/2024")
     *   ),
     *   @OA\Parameter(
     *     name="caseType",
     *     in="query",
     *     required=false,
     *     description="Filter by caseType (partial match)",
     *     @OA\Schema(type="string", example="OA")
     *   ),
     *   @OA\Parameter(
     *     name="casestatus",
     *     in="query",
     *     required=false,
     *     description="Filter by case status (partial match)",
     *     @OA\Schema(type="string", example="Pending")
     *   ),
     *   @OA\Parameter(
     *     name="courtName",
     *     in="query",
     *     required=false,
     *     description="Filter by court name (partial match)",
     *     @OA\Schema(type="string", example="CAT Jammu")
     *   ),
     *   @OA\Parameter(
     *     name="filing_date",
     *     in="query",
     *     required=false,
     *     description="Filter by exact filing date (YYYY-MM-DD format)",
     *     @OA\Schema(type="string", format="date", example="2024-01-15")
     *   ),
     *   @OA\Parameter(
     *     name="filing_date_from",
     *     in="query",
     *     required=false,
     *     description="Filter by filing date from (YYYY-MM-DD format)",
     *     @OA\Schema(type="string", format="date", example="2024-01-01")
     *   ),
     *   @OA\Parameter(
     *     name="filing_date_to",
     *     in="query",
     *     required=false,
     *     description="Filter by filing date to (YYYY-MM-DD format, must be >= filing_date_from)",
     *     @OA\Schema(type="string", format="date", example="2024-12-31")
     *   ),
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     required=false,
     *     description="Results per page (default 20, max 100)",
     *     @OA\Schema(type="integer", minimum=1, maximum=100, example=20)
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     required=false,
     *     description="Page number (default 1)",
     *     @OA\Schema(type="integer", minimum=1, example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful search",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="current_page", type="integer", example=1),
     *         @OA\Property(property="per_page", type="integer", example=20),
     *         @OA\Property(property="total", type="integer", example=200)
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="errors", type="object")
     *     )
     *   )
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'applicant' => ['nullable', 'string', 'max:255'],
            'respondent' => ['nullable', 'string', 'max:255'],
            'applicantadvocate1' => ['nullable', 'string', 'max:255'],
            'applicantadvocate' => ['nullable', 'string', 'max:255'],
            'respondentadvocate' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'case_type' => ['nullable', 'string', 'max:255'],
            'case_no' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'caseno' => ['nullable', 'string', 'max:255'],
            'caseType' => ['nullable', 'string', 'max:255'],
            'casestatus' => ['nullable', 'string', 'max:255'],
            'courtName' => ['nullable', 'string', 'max:255'],
            'filing_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'filing_date_from' => ['nullable', 'date', 'date_format:Y-m-d'],
            'filing_date_to' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:filing_date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = $validator->validated();
        $perPage = $filters['per_page'] ?? 20;

        $query = DB::table('cases');

        // Like filters for partial matching
        $likeFilters = [
            'applicant',
            'respondent',
            'applicantadvocate1',
            'applicantadvocate',
            'respondentadvocate',
            'location',
            'case_type',
            'caseType',
            'casestatus',
            'courtName',
        ];

        // Safe field names whitelist to prevent SQL injection
        $allowedFields = [
            'applicant' => 'applicant',
            'respondent' => 'respondent',
            'applicantadvocate1' => 'applicantadvocate1',
            'applicantadvocate' => 'applicantadvocate',
            'respondentadvocate' => 'respondentadvocate',
            'location' => 'location',
            'case_type' => 'case_type',
            'caseType' => 'caseType',
            'casestatus' => 'casestatus',
            'courtName' => 'courtName',
        ];

        foreach ($likeFilters as $inputField) {
            if (!empty($filters[$inputField])) {
                // Only use whitelisted field names - prevents SQL injection
                if (!isset($allowedFields[$inputField])) {
                    continue; // Skip unknown fields
                }
                $dbField = $allowedFields[$inputField];
                $value = mb_strtolower(trim($filters[$inputField]));
                // Use parameterized query with whitelisted column name - value is safely bound
                // Column name is from whitelist (safe), value uses ? placeholder (safe from SQL injection)
                // Using backticks for column name since it's from a whitelist
                $query->whereRaw('LOWER(`' . $dbField . '`) LIKE ?', ['%' . $value . '%']);
            }
        }

        // Exact filters
        $exactFilters = [
            'case_no' => 'case_no',
            'year' => 'year',
            'caseno' => 'caseno',
        ];

        foreach ($exactFilters as $inputKey => $column) {
            if (isset($filters[$inputKey]) && $filters[$inputKey] !== '') {
                // Use parameterized query - safe from SQL injection
                $query->where($column, $filters[$inputKey]);
            }
        }

        // Handle filing_date filters safely
        if (!empty($filters['filing_date'])) {
            // Use whereDate for exact date match - safe from SQL injection
            $query->whereDate('dateoffiling', $filters['filing_date']);
        }

        // Handle date range filters safely
        if (!empty($filters['filing_date_from'])) {
            $query->whereDate('dateoffiling', '>=', $filters['filing_date_from']);
        }

        if (!empty($filters['filing_date_to'])) {
            $query->whereDate('dateoffiling', '<=', $filters['filing_date_to']);
        }

        // Select all columns with combined fields
        // Use applicantadvocate1 if available, otherwise applicantadvocate
        // Use nextlistingdate1 if available, otherwise nextlistingdate2
        $query->select([
            'id',
            'location',
            'case_type',
            'case_no',
            'year',
            'caseno',
            'caseType',
            'casestatus',
            'applicant',
            'respondent',
            DB::raw('COALESCE(NULLIF(applicantadvocate1, \'\'), applicantadvocate) as applicantadvocate'),
            'respondentadvocate',
            DB::raw('COALESCE(NULLIF(nextlistingdate1, \'\'), nextlistingdate2) as nextlistingdate'),
            'lastlistingdate',
            'additionalpartypet',
            'additionalpartyres',
            'dateofdisposal',
            'nextListingPurpose',
            'courtNo',
            'courtName',
            'disposalNature',
            'dateoffiling',
            'petitioner_file',
            'reply_file',
        ])->orderByDesc('dateoffiling');

        $results = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $results->items(),
            'meta' => [
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
            ],
        ]);
    }
}
