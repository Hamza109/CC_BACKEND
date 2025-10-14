<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class CourtMapController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/courts/coordinates",
     *   summary="Get court coordinates",
     *   tags={"Courts"},
     *   @OA\Parameter(name="district", in="query", description="Filter by district", @OA\Schema(type="string")),
     *   @OA\Parameter(name="q", in="query", description="Search in court name", @OA\Schema(type="string")),
     *   @OA\Response(
     *     response=200,
     *     description="List of courts with coordinates",
     *     @OA\JsonContent(type="array", @OA\Items(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="court_name", type="string"),
     *       @OA\Property(property="address", type="string"),
     *       @OA\Property(property="lat", type="number", format="float"),
     *       @OA\Property(property="lng", type="number", format="float"),
     *       @OA\Property(property="district", type="string")
     *     ))
     *   )
     * )
     */
    public function index(Request $request)
    {
        $district = $request->query('district');
        $q = $request->query('q');

        $query = DB::table('citizen.tbl_map')
            ->select(['id', 'court_name', 'address', 'lat', 'lng', 'district']);

        if ($district) {
            $query->where('district', $district);
        }
        if ($q) {
            $query->where('court_name', 'like', '%' . $q . '%');
        }

        $results = $query->limit(1000)->get();

        return response()->json($results);
    }

    /**
     * @OA\Get(
     *   path="/api/courts/districts",
     *   summary="Get list of districts with courts",
     *   tags={"Courts"},
     *   @OA\Response(
     *     response=200,
     *     description="Distinct districts",
     *     @OA\JsonContent(type="array", @OA\Items(type="string"))
     *   )
     * )
     */
    public function districts()
    {
        $districts = DB::table('citizen.tbl_map')
            ->select('district')
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->distinct()
            ->orderBy('district')
            ->pluck('district')
            ->values();

        return response()->json($districts);
    }
}
