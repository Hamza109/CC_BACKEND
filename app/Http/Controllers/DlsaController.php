<?php

namespace App\Http\Controllers;

use App\Models\Dlsa;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DlsaController extends Controller
{
    /**
     * Return list of DLSA entries, optionally filtered by district.
     */
    /**
     * @OA\Get(
     *   path="/api/dlsa",
     *   summary="List DLSA records",
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

        $query = Dlsa::query();

        if ($district !== null && $district !== '') {
            $query->where('name_dlsa', $district);
        }

        $items = $query
            ->select([
                'dlsa_id',
                'office',
                'name_dlsa',
                'name',
                'mobile_no',
                'alternate_no',
                'lat',
                'lng',
                'designation',
            ])
            ->orderBy('name_dlsa')
            ->orderBy('office')
            ->get();

        return JsonResource::collection($items);
    }

    /**
     * Return distinct list of districts that have DLSA entries.
     */
    /**
     * @OA\Get(
     *   path="/api/dlsa/districts",
     *   summary="List districts with DLSA entries",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function districts(): \Illuminate\Http\JsonResponse
    {
        $districts = Dlsa::query()
            ->whereNotNull('name_dlsa')
            ->where('name_dlsa', '!=', '')
            ->distinct()
            ->orderBy('name_dlsa')
            ->pluck('name_dlsa');

        return response()->json($districts->values());
    }
}
