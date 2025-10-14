<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class MlaController extends Controller
{
	/**
	 * @OA\Get(
	 *   path="/api/mlas",
	 *   summary="List MLA details",
	 *   tags={"MLAs"},
	 *   @OA\Parameter(name="constituency", in="query", description="Filter by constituency", @OA\Schema(type="string")),
	 *   @OA\Parameter(name="q", in="query", description="Search in name", @OA\Schema(type="string")),
	 *   @OA\Response(
	 *     response=200,
	 *     description="MLAs list",
	 *     @OA\JsonContent(type="array", @OA\Items(
	 *       type="object",
	 *       @OA\Property(property="id", type="integer"),
	 *       @OA\Property(property="constituency", type="string"),
	 *       @OA\Property(property="name", type="string"),
	 *       @OA\Property(property="mobile_no", type="string"),
	 *       @OA\Property(property="address", type="string", nullable=true),
	 *       @OA\Property(property="email", type="string", nullable=true)
	 *     ))
	 *   )
	 * )
	 */
	public function index(Request $request)
	{
		$constituency = $request->query('constituency');
		$q = $request->query('q');

		$query = DB::table('citizen.tbl_mla')
			->select(['id', 'constituency', 'name', 'mobile_no', 'address', 'email']);

		if ($constituency) {
			$query->where('constituency', $constituency);
		}
		if ($q) {
			$query->where('name', 'like', '%' . $q . '%');
		}

		$results = $query->limit(1000)->get();
		return response()->json($results);
	}

}