<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class StandingCounselController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/standing-counsels",
     *   summary="List standing counsels (paginated)",
     *   tags={"Standing Counsels"},
     *   @OA\Parameter(name="district", in="query", description="Filter by district", @OA\Schema(type="string")),
     *   @OA\Parameter(name="q", in="query", description="Search in counsel_name or allocation_work", @OA\Schema(type="string")),
     *   @OA\Parameter(name="page", in="query", description="Page number (default 1)", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="per_page", in="query", description="Items per page (default 20, max 100)", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Response(
     *     response=200,
     *     description="Paginated standing counsels",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="data", type="array", @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="district", type="string"),
     *         @OA\Property(property="serial_no", type="string"),
     *         @OA\Property(property="counsel_name", type="string"),
     *         @OA\Property(property="allocation_work", type="string"),
     *         @OA\Property(property="contact_no", type="string"),
     *         @OA\Property(property="email_id", type="string", nullable=true),
     *         @OA\Property(property="designation", type="string", nullable=true),
     *         @OA\Property(property="created_at", type="string", format="date-time")
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
        $qText = $request->query('q');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $baseQuery = DB::table('citizen.standing_counsels')
            ->select(['id', 'district', 'serial_no', 'counsel_name', 'allocation_work', 'contact_no', 'email_id', 'designation', 'created_at']);

        if ($district) {
            $baseQuery->where('district', $district);
        }
        if ($qText) {
            $baseQuery->where(function ($sub) use ($qText) {
                $sub->where('counsel_name', 'like', '%' . $qText . '%')
                    ->orWhere('allocation_work', 'like', '%' . $qText . '%');
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

    /**
     * @OA\Get(
     *   path="/api/standing-counsels/districts",
     *   summary="Get standing counsel districts",
     *   tags={"Standing Counsels"},
     *   @OA\Response(
     *     response=200,
     *     description="Distinct districts",
     *     @OA\JsonContent(type="array", @OA\Items(type="string"))
     *   )
     * )
     */
    public function districts()
    {
        $districts = DB::table('citizen.standing_counsels')
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
