<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class HcCaseController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/hc-cases/search",
     *   summary="Search High Court cases",
     *   description="Searches the hc_case_full_details table using optional filters.",
     *   tags={"High Court"},
     *   @OA\Parameter(
     *     name="pet_name",
     *     in="query",
     *     required=false,
     *     description="Filter by petitioner name (partial match)",
     *     @OA\Schema(type="string", example="Amit")
     *   ),
     *   @OA\Parameter(
     *     name="pet_adv",
     *     in="query",
     *     required=false,
     *     description="Filter by petitioner advocate (partial match)",
     *     @OA\Schema(type="string", example="Adv. Sharma")
     *   ),
     *   @OA\Parameter(
     *     name="res_name",
     *     in="query",
     *     required=false,
     *     description="Filter by respondent name (partial match)",
     *     @OA\Schema(type="string", example="State of J&K")
     *   ),
     *   @OA\Parameter(
     *     name="res_adv",
     *     in="query",
     *     required=false,
     *     description="Filter by respondent advocate (partial match)",
     *     @OA\Schema(type="string", example="Adv. Khan")
     *   ),
     *   @OA\Parameter(
     *     name="sub_category",
     *     in="query",
     *     required=false,
     *     description="Filter by sub category (partial match)",
     *     @OA\Schema(type="string", example="Civil")
     *   ),
     *   @OA\Parameter(
     *     name="est_code",
     *     in="query",
     *     required=false,
     *     description="Filter by establishment code (exact match)",
     *     @OA\Schema(type="string", example="JKOJ")
     *   ),
     *   @OA\Parameter(
     *     name="case_type",
     *     in="query",
     *     required=false,
     *     description="Filter by case type id (exact match)",
     *     @OA\Schema(type="integer", example=171)
     *   ),
     *   @OA\Parameter(
     *     name="reg_year",
     *     in="query",
     *     required=false,
     *     description="Filter by registration year (exact match)",
     *     @OA\Schema(type="integer", example=2023)
     *   ),
     *   @OA\Parameter(
     *     name="reg_no",
     *     in="query",
     *     required=false,
     *     description="Filter by registration number (exact match)",
     *     @OA\Schema(type="string", example="328")
     *   ),
     *   @OA\Parameter(
     *     name="cino",
     *     in="query",
     *     required=false,
     *     description="Filter by CINO (exact match)",
     *     @OA\Schema(type="string", example="JKHC010002862013")
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
            'pet_name' => ['nullable', 'string'],
            'pet_adv' => ['nullable', 'string'],
            'res_name' => ['nullable', 'string'],
            'res_adv' => ['nullable', 'string'],
            'sub_category' => ['nullable', 'string'],
            'est_code' => ['nullable', 'string'],
            'case_type' => ['nullable', 'integer'],
            'reg_year' => ['nullable', 'integer'],
            'reg_no' => ['nullable', 'string'],
            'cino' => ['nullable', 'string'],
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

        $query = DB::table('citizen.hc_case_full_details');

        $likeFilters = [
            'pet_name',
            'pet_adv',
            'res_name',
            'res_adv',
            'sub_category',
        ];

        foreach ($likeFilters as $field) {
            if (!empty($filters[$field])) {
                $value = mb_strtolower($filters[$field]);
                $query->whereRaw('LOWER(' . $field . ') LIKE ?', ['%' . $value . '%']);
            }
        }

        $exactFilters = [
            'est_code' => 'est_code',
            'case_type' => 'case_type_id',
            'reg_year' => 'reg_year',
            'reg_no' => 'reg_no',
            'cino' => 'cino',
        ];

        foreach ($exactFilters as $inputKey => $column) {
            if (isset($filters[$inputKey]) && $filters[$inputKey] !== '') {
                $query->where($column, $filters[$inputKey]);
            }
        }

        $columns = [
            'date_of_filing', 'cino', 'dt_regis', 'type_name_fil', 'type_name_reg',
            'case_type_id', 'fil_no', 'fil_year', 'reg_no', 'reg_year', 'date_first_list',
            'date_next_list', 'pend_disp', 'date_of_decision', 'disposal_type',
            'bench_type', 'causelist_type', 'bench_name', 'judicial_branch', 'coram',
            'short_order', 'desgname', 'bench_id', 'court_est_name', 'est_code',
            'state_name', 'dist_name', 'purpose_name', 'pet_name', 'pet_adv',
            'pet_legal_heir', 'res_name', 'res_name_entity', 'res_name_department',
            'res_adv', 'res_legal_heir', 'main_matter_cino', 'main_matter', 'fir_no',
            'police_station', 'uniform_code', 'police_st_code', 'fir_year',
            'lower_court_name', 'lower_court_caseno', 'lower_court_dec_dt',
            'trial_lower_court_name', 'trial_lower_court_caseno',
            'trial_lower_court_dec_dt', 'date_last_list', 'date_filing_disp',
            'reason_for_rej', 'act1_name', 'act1_section', 'hearing1_causelist_type',
            'hearing1_judge_name', 'hearing1_business_date', 'hearing1_hearing_date',
            'hearing1_purpose', 'hearing2_causelist_type', 'hearing2_judge_name',
            'hearing2_business_date', 'hearing2_hearing_date', 'hearing2_purpose',
            'finalorder_no', 'finalorder_date', 'finalorder_details', 'category',
            'sub_category', 'file_path', 'file_path1',
        ];

        $query->select($columns)->orderByDesc('date_of_filing');

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


