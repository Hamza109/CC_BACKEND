<?php

namespace App\Http\Controllers;

use App\Models\DistrictLitigationOfficer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictLitigationOfficerController extends Controller
{
    /**
     * Return list of district litigation officers, optionally filtered by district.
     */
    /**
     * @OA\Get(
     *   path="/api/district-litigation-officers",
     *   summary="List district litigation officers",
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

        $query = DistrictLitigationOfficer::query();

        if ($district !== null && $district !== '') {
            $query->where('district_name', $district);
        }

        $officers = $query
            ->select(['id', 'office_name', 'contact_number', 'lat', 'lng', 'district_name'])
            ->orderBy('district_name')
            ->orderBy('office_name')
            ->get();

        return JsonResource::collection($officers);
    }

    /**
     * Return distinct list of districts that have litigation officers.
     */
    /**
     * @OA\Get(
     *   path="/api/district-litigation-officers/districts",
     *   summary="List districts with litigation officers",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function districts(): \Illuminate\Http\JsonResponse
    {
        $districts = DistrictLitigationOfficer::query()
            ->whereNotNull('district_name')
            ->where('district_name', '!=', '')
            ->distinct()
            ->orderBy('district_name')
            ->pluck('district_name');

        return response()->json($districts->values());
    }
}

