<?php

namespace App\Http\Controllers;

use App\Models\LegalAidClinic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="CCS API",
 * )
 */
class LegalAidClinicController extends Controller
{
    /**
     * Return list of legal aid clinics, optionally filtered by district.
     */
    /**
     * @OA\Get(
     *   path="/api/legal-aid-clinics",
     *   summary="List legal aid clinics",
     *   @OA\Parameter(
     *     name="district",
     *     in="query",
     *     required=false,
     *     description="Filter by district name",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request): JsonResource
    {
        $district = $request->query('district');

        $query = LegalAidClinic::query();

        if ($district !== null && $district !== '') {
            $query->where('district_name', $district);
        }

        $clinics = $query
            ->select(['aid_id', 'name', 'address', 'lat', 'lng', 'district_name'])
            ->orderBy('district_name')
            ->orderBy('name')
            ->get();

        return JsonResource::collection($clinics);
    }

    /**
     * Return distinct list of districts that have legal aid clinics.
     */
    /**
     * @OA\Get(
     *   path="/api/districts",
     *   summary="List districts",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function districts(): \Illuminate\Http\JsonResponse
    {
        $districts = LegalAidClinic::query()
            ->whereNotNull('district_name')
            ->where('district_name', '!=', '')
            ->distinct()
            ->orderBy('district_name')
            ->pluck('district_name');

        return response()->json($districts->values());
    }
}


