<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class DcpuController extends Controller
{
	/**
	 * @OA\Get(
	 *   path="/api/dcpu",
	 *   summary="List DCPU directory",
	 *   tags={"DCPU"},
	 *   @OA\Parameter(name="district", in="query", description="Filter by district", @OA\Schema(type="string")),
	 *   @OA\Parameter(name="organisation", in="query", description="Filter by organisation", @OA\Schema(type="string")),
	 *   @OA\Parameter(name="q", in="query", description="Search in officer name or designation", @OA\Schema(type="string")),
	 *   @OA\Response(
	 *     response=200,
	 *     description="DCPU directory list",
	 *     @OA\JsonContent(type="array", @OA\Items(
	 *       type="object",
	 *       @OA\Property(property="id", type="integer"),
	 *       @OA\Property(property="district", type="string"),
	 *       @OA\Property(property="organisation", type="string"),
	 *       @OA\Property(property="officer_name", type="string"),
	 *       @OA\Property(property="designation", type="string"),
	 *       @OA\Property(property="mobile", type="string"),
	 *       @OA\Property(property="email", type="string", nullable=true),
	 *       @OA\Property(property="lat", type="number", format="float", nullable=true),
	 *       @OA\Property(property="lng", type="number", format="float", nullable=true),
	 *       @OA\Property(property="address", type="string", nullable=true)
	 *     ))
	 *   )
	 * )
	 */
	public function index(Request $request)
	{
		$district = $request->query('district');
		$organisation = $request->query('organisation');
		$q = $request->query('q');

		$query = DB::table('citizen.dcpu_directory')
			->select(['id', 'district', 'organisation', 'officer_name', 'designation', 'mobile', 'email', 'lat', 'lng', 'address']);

		if ($district) {
			$query->where('district', $district);
		}
		if ($organisation) {
			$query->where('organisation', $organisation);
		}
		if ($q) {
			$query->where(function ($sub) use ($q) {
				$sub->where('officer_name', 'like', '%' . $q . '%')
					->orWhere('designation', 'like', '%' . $q . '%');
			});
		}

		$results = $query->limit(1000)->get();
		return response()->json($results);
	}

	/**
	 * @OA\Get(
	 *   path="/api/dcpu/districts",
	 *   summary="Get DCPU districts",
	 *   tags={"DCPU"},
	 *   @OA\Response(
	 *     response=200,
	 *     description="Distinct districts",
	 *     @OA\JsonContent(type="array", @OA\Items(type="string"))
	 *   )
	 * )
	 */
	public function districts()
	{
		$districts = DB::table('citizen.dcpu_directory')
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