<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class ConsumerController extends Controller
{
	/**
	 * @OA\Get(
	 *   path="/api/consumers",
	 *   summary="List consumer details",
	 *   tags={"Consumers"},
	 *   @OA\Parameter(name="district", in="query", description="Filter by district", @OA\Schema(type="string")),
	 *   @OA\Parameter(name="q", in="query", description="Search in name/designation/email/mobile_no", @OA\Schema(type="string")),
	 *   @OA\Response(
	 *     response=200,
	 *     description="Consumers list",
	 *     @OA\JsonContent(type="array", @OA\Items(
	 *       type="object",
	 *       @OA\Property(property="id", type="integer"),
	 *       @OA\Property(property="name", type="string"),
	 *       @OA\Property(property="mobile_no", type="string"),
	 *       @OA\Property(property="email", type="string"),
	 *       @OA\Property(property="district", type="string"),
	 *       @OA\Property(property="designation", type="string"),
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

		$query = DB::table('citizen.tbl_consumer_details')
			->select(['id', 'name', 'mobile_no', 'email', 'district', 'designation', 'lat', 'lng']);

		if ($district) {
			$query->where('district', $district);
		}
		if ($q) {
			$query->where(function ($sub) use ($q) {
				$sub->where('name', 'like', '%' . $q . '%')
					->orWhere('designation', 'like', '%' . $q . '%')
					->orWhere('email', 'like', '%' . $q . '%')
					->orWhere('mobile_no', 'like', '%' . $q . '%');
			});
		}

		$results = $query->limit(1000)->get();
		return response()->json($results);
	}

	/**
	 * @OA\Get(
	 *   path="/api/consumers/districts",
	 *   summary="Get consumer districts",
	 *   tags={"Consumers"},
	 *   @OA\Response(
	 *     response=200,
	 *     description="Distinct districts",
	 *     @OA\JsonContent(type="array", @OA\Items(type="string"))
	 *   )
	 * )
	 */
	public function districts()
	{
		$districts = DB::table('citizen.tbl_consumer_details')
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
