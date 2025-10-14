<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class OscController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/osc",
     *   summary="List OSC staff",
     *   tags={"OSC"},
     *   @OA\Parameter(name="district", in="query", description="Filter by district", @OA\Schema(type="string")),
     *   @OA\Parameter(name="q", in="query", description="Search in name/designation", @OA\Schema(type="string")),
     *   @OA\Response(
     *     response=200,
     *     description="OSC staff list",
     *     @OA\JsonContent(type="array", @OA\Items(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="district_name", type="string"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="designation", type="string"),
     *       @OA\Property(property="mobile_number", type="string"),
     *       @OA\Property(property="alternate_number", type="string", nullable=true),
     *       @OA\Property(property="address", type="string", nullable=true),
     *       @OA\Property(property="lat", type="number", format="float", nullable=true),
     *       @OA\Property(property="lng", type="number", format="float", nullable=true)
     *     ))
     *   )
     * )
     */
    public function index(Request $request)
    {
        $district = $request->query('district');
        $q = $request->query('q');

        $query = DB::table('citizen.osc_staff')
            ->select(['id', 'district_name', 'name', 'designation', 'mobile_number', 'alternate_number', 'address', 'lat', 'lng']);

        if ($district) {
            $query->where('district_name', $district);
        }
        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('designation', 'like', '%' . $q . '%');
            });
        }

        $results = $query->limit(1000)->get();
        return response()->json($results);
    }

    /**
     * @OA\Get(
     *   path="/api/osc/districts",
     *   summary="Get OSC districts",
     *   tags={"OSC"},
     *   @OA\Response(
     *     response=200,
     *     description="Distinct districts",
     *     @OA\JsonContent(type="array", @OA\Items(type="string"))
     *   )
     * )
     */
    public function districts()
    {
        $districts = DB::table('citizen.osc_staff')
            ->select('district_name')
            ->whereNotNull('district_name')
            ->where('district_name', '!=', '')
            ->distinct()
            ->orderBy('district_name')
            ->pluck('district_name')
            ->values();

        return response()->json($districts);
    }
}
