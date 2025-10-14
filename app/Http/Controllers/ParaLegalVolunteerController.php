<?php

namespace App\Http\Controllers;

use App\Models\ParaLegalVolunteer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParaLegalVolunteerController extends Controller
{
    /**
     * Return list of para legal volunteers, optionally filtered by district.
     */
    /**
     * @OA\Get(
     *   path="/api/para-legal-volunteers",
     *   summary="List para legal volunteers",
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

        $query = ParaLegalVolunteer::query();

        if ($district !== null && $district !== '') {
            $query->where('district_name', $district);
        }

        $volunteers = $query
            ->select(['id', 'name', 'mobile_number', 'empanelment', 'district_name', 'created_at'])
            ->orderBy('district_name')
            ->orderBy('name')
            ->get();

        return JsonResource::collection($volunteers);
    }

    /**
     * Return distinct list of districts that have para legal volunteers.
     */
    /**
     * @OA\Get(
     *   path="/api/para-legal-volunteers/districts",
     *   summary="List districts with para legal volunteers",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function districts(): \Illuminate\Http\JsonResponse
    {
        $districts = ParaLegalVolunteer::query()
            ->whereNotNull('district_name')
            ->where('district_name', '!=', '')
            ->distinct()
            ->orderBy('district_name')
            ->pluck('district_name');

        return response()->json($districts->values());
    }
}
