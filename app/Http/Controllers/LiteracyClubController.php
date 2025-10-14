<?php

namespace App\Http\Controllers;

use App\Models\LiteracyClub;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiteracyClubController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/literacy-clubs",
     *   summary="List legal literacy clubs",
     *   @OA\Parameter(name="district", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request): JsonResource
    {
        $district = $request->query('district');

        $query = LiteracyClub::query();
        if ($district !== null && $district !== '') {
            $query->where('district_name', $district);
        }

        $clubs = $query
            ->select(['club_id', 'name', 'lat', 'lng', 'district_name'])
            ->orderBy('district_name')
            ->orderBy('name')
            ->get();

        return JsonResource::collection($clubs);
    }

    /**
     * @OA\Get(
     *   path="/api/literacy-clubs/districts",
     *   summary="List districts with literacy clubs",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function districts(): \Illuminate\Http\JsonResponse
    {
        $districts = LiteracyClub::query()
            ->whereNotNull('district_name')
            ->where('district_name', '!=', '')
            ->distinct()
            ->orderBy('district_name')
            ->pluck('district_name');

        return response()->json($districts->values());
    }
}
