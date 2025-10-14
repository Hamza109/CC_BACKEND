<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class EcourtBranchController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/ecourt-branches",
     *   summary="List eCourt branches (paginated)",
     *   tags={"eCourt Branches"},
     *   @OA\Parameter(name="q", in="query", description="Search in account_name or branch_location", @OA\Schema(type="string")),
     *   @OA\Parameter(name="page", in="query", description="Page number (default 1)", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="per_page", in="query", description="Items per page (default 20, max 100)", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Response(
     *     response=200,
     *     description="Paginated branches",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="data", type="array", @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="account_name", type="string"),
     *         @OA\Property(property="branch_location", type="string"),
     *         @OA\Property(property="mobile_no", type="string")
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
        $qText = $request->query('q');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $baseQuery = DB::table('citizen.ecourt_branches')
            ->select(['id', 'account_name', 'branch_location', 'mobile_no']);

        if ($qText) {
            $baseQuery->where(function ($sub) use ($qText) {
                $sub->where('account_name', 'like', '%' . $qText . '%')
                    ->orWhere('branch_location', 'like', '%' . $qText . '%');
            });
        }

        $total = (clone $baseQuery)->count();
        $data = $baseQuery->orderBy('id')->forPage($page, $perPage)->get();

        return response()->json([
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int) $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }
}
