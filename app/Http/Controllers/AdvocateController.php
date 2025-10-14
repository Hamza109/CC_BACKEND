<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class AdvocateController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/advocates",
     *   summary="List advocates (paginated)",
     *   tags={"Advocates"},
     *   @OA\Parameter(name="year", in="query", description="Filter by enrolment year", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="q", in="query", description="Search in name, enrolment_no or residence", @OA\Schema(type="string")),
     *   @OA\Parameter(name="page", in="query", description="Page number (default 1)", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="per_page", in="query", description="Items per page (default 20, max 100)", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Response(
     *     response=200,
     *     description="Paginated advocates",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="data", type="array", @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="year", type="integer"),
     *         @OA\Property(property="serial_no", type="string"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="enrolment_no", type="string"),
     *         @OA\Property(property="enrolment_year", type="string"),
     *         @OA\Property(property="parentage", type="string"),
     *         @OA\Property(property="residence", type="string"),
     *         @OA\Property(property="phone", type="string", nullable=true),
     *         @OA\Property(property="mobile", type="string", nullable=true),
     *         @OA\Property(property="email", type="string", nullable=true)
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
        $year = $request->query('year');
        $qText = $request->query('q');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $baseQuery = DB::table('citizen.advocates')
            ->select(['id', 'year', 'serial_no', 'name', 'enrolment_no', 'enrolment_year', 'parentage', 'residence', 'phone', 'mobile', 'email']);

        if ($year) {
            $baseQuery->where('year', (int) $year);
        }
        if ($qText) {
            $baseQuery->where(function ($sub) use ($qText) {
                $sub->where('name', 'like', '%' . $qText . '%')
                    ->orWhere('enrolment_no', 'like', '%' . $qText . '%')
                    ->orWhere('residence', 'like', '%' . $qText . '%');
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
