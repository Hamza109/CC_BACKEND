<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class EstampVendorController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/estamp-vendors",
     *   summary="List e-stamp vendors (paginated)",
     *   tags={"E-Stamp Vendors"},
     *   @OA\Parameter(name="district", in="query", description="Filter by district", @OA\Schema(type="string")),
     *   @OA\Parameter(name="q", in="query", description="Search in account_name or branch_address", @OA\Schema(type="string")),
     *   @OA\Parameter(name="page", in="query", description="Page number (default 1)", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="per_page", in="query", description="Items per page (default 20, max 100)", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Response(
     *     response=200,
     *     description="Paginated vendor list",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="data", type="array", @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="account_name", type="string"),
     *         @OA\Property(property="acctid", type="string"),
     *         @OA\Property(property="branchcd", type="string"),
     *         @OA\Property(property="branch_address", type="string"),
     *         @OA\Property(property="branch_phone", type="string"),
     *         @OA\Property(property="account_phone", type="string")
     *       )),
     *       @OA\Property(property="pagination", type="object",
     *         @OA\Property(property="page", type="integer"),
     *         @OA\Property(property="per_page", type="integer"),
     *         @OA\Property(property="total", type="integer"),
     *         @OA\Property(property="total_pages", type="integer")
     *       )
     *     )
     *   )
     * )
     */
    public function index(Request $request)
    {
        $district = $request->query('district');
        $q = $request->query('q');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $baseQuery = DB::table('citizen.estamp_vendors')
            ->select(['id', 'account_name', 'acctid', 'branchcd', 'branch_address', 'branch_phone', 'account_phone']);

        if ($district) {
            // Many datasets embed district in branch_address; if there is an explicit column, adjust here
            $baseQuery->where('branch_address', 'like', '%' . $district . '%');
        }
        if ($q) {
            $baseQuery->where(function ($sub) use ($q) {
                $sub->where('account_name', 'like', '%' . $q . '%')
                    ->orWhere('branch_address', 'like', '%' . $q . '%');
            });
        }

        // Clone query for count
        $countQuery = clone $baseQuery;
        $total = (int) $countQuery->count();

        $data = $baseQuery
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/estamp-vendors/districts",
     *   summary="Get e-stamp vendor districts (best-effort from branch_address)",
     *   tags={"E-Stamp Vendors"},
     *   @OA\Response(
     *     response=200,
     *     description="List of district-like values",
     *     @OA\JsonContent(type="array", @OA\Items(type="string"))
     *   )
     * )
     */
    public function districts()
    {
        // If the table has an explicit district column, replace this with distinct() on that column
        $addresses = DB::table('citizen.estamp_vendors')
            ->select('branch_address')
            ->whereNotNull('branch_address')
            ->where('branch_address', '!=', '')
            ->pluck('branch_address');

        // Simple heuristic: extract the last token (after last comma) as a potential district name
        $districts = $addresses->map(function ($addr) {
            $parts = array_map('trim', explode(',', $addr));
            return end($parts);
        })->filter()->unique()->sort()->values();

        return response()->json($districts);
    }
}
