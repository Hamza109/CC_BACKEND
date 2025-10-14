<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class RegistrarController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/registrars",
     *   summary="List registrar directory",
     *   tags={"Registrars"},
     *   @OA\Parameter(name="district", in="query", description="Filter by district_name", @OA\Schema(type="string")),
     *   @OA\Parameter(name="division", in="query", description="Filter by division", @OA\Schema(type="string")),
     *   @OA\Parameter(name="q", in="query", description="Search in registration_id, name or designation", @OA\Schema(type="string")),
     *   @OA\Response(
     *     response=200,
     *     description="Registrar list",
     *     @OA\JsonContent(type="array", @OA\Items(
     *       type="object",
     *       @OA\Property(property="registrar_id", type="integer"),
     *   
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="designation", type="string"),
     *       @OA\Property(property="mobile_no", type="string"),
     *       @OA\Property(property="email", type="string", nullable=true),
     *       @OA\Property(property="lat", type="number", format="float", nullable=true),
     *       @OA\Property(property="lng", type="number", format="float", nullable=true),
     *       @OA\Property(property="district_name", type="string"),
     *       @OA\Property(property="division", type="string"),
     *       @OA\Property(property="serial_no", type="string")
     *     ))
     *   )
     * )
     */
    public function index(Request $request)
    {
        $district = $request->query('district');
        $division = $request->query('division');
        $q = $request->query('q');

        $query = DB::table('citizen.tbl_registrar')
            ->select(['registrar_id', 'name', 'designation', 'mobile_no', 'email', 'lat', 'lng', 'district_name', 'division', 'serial_no']);

        if ($district) {
            $query->where('district_name', $district);
        }
        if ($division) {
            $query->where('division', $division);
        }
        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('registrar_id', 'like', '%' . $q . '%')
                    ->orWhere('name', 'like', '%' . $q . '%')
                    ->orWhere('designation', 'like', '%' . $q . '%');
            });
        }

        $results = $query->limit(1000)->get();
        return response()->json($results);
    }

    /**
     * @OA\Get(
     *   path="/api/registrars/districts",
     *   summary="Get registrar districts",
     *   tags={"Registrars"},
     *   @OA\Response(
     *     response=200,
     *     description="Distinct districts",
     *     @OA\JsonContent(type="array", @OA\Items(type="string"))
     *   )
     * )
     */
    public function districts()
    {
        $districts = DB::table('citizen.tbl_registrar')
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
