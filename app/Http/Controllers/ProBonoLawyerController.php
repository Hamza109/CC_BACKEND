<?php

namespace App\Http\Controllers;

use App\Models\ProBonoLawyer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProBonoLawyerController extends Controller
{
    /**
     * Return list of pro bono lawyers, optionally filtered by district.
     */
    /**
     * @OA\Get(
     *   path="/api/pro-bono-lawyers",
     *   summary="List pro bono lawyers",
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

        $query = ProBonoLawyer::query();

        if ($district !== null && $district !== '') {
            $query->where('district_name', $district);
        }

        $lawyers = $query
            ->select(['id', 'name', 'district_name', 'mobile_number', 'created_at'])
            ->orderBy('district_name')
            ->orderBy('name')
            ->get();

        return JsonResource::collection($lawyers);
    }

    /**
     * Return distinct list of districts that have pro bono lawyers.
     */
    /**
     * @OA\Get(
     *   path="/api/pro-bono-lawyers/districts",
     *   summary="List districts with pro bono lawyers",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function districts(): \Illuminate\Http\JsonResponse
    {
        $districts = ProBonoLawyer::query()
            ->whereNotNull('district_name')
            ->where('district_name', '!=', '')
            ->distinct()
            ->orderBy('district_name')
            ->pluck('district_name');

        return response()->json($districts->values());
    }
}

