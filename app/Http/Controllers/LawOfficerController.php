<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class LawOfficerController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/law-officers",
     *   summary="List law officers (paginated)",
     *   tags={"Law Officers"},
     *   @OA\Parameter(name="wing", in="query", description="Filter by wing (e.g., Jammu/Srinagar)", @OA\Schema(type="string")),
     *   @OA\Parameter(name="q", in="query", description="Search in name, designation or office address", @OA\Schema(type="string")),
     *   @OA\Parameter(name="page", in="query", description="Page number (default 1)", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="per_page", in="query", description="Items per page (default 20, max 100)", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Response(
     *     response=200,
     *     description="Paginated law officers",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="data", type="array", @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="designation", type="string"),
     *         @OA\Property(property="contact_no", type="string"),
     *         @OA\Property(property="email_address", type="string", nullable=true),
     *         @OA\Property(property="office_address", type="string"),
     *         @OA\Property(property="wing", type="string"),
     *         @OA\Property(property="s_no", type="integer")
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
        $wing = $request->query('wing');
        $qText = $request->query('q');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $baseQuery = DB::table('citizen.law_officers')
            ->select([
                'id',
                'Name as name',
                'Designation as designation',
                'Contact_No as contact_no',
                'Email_Address as email_address',
                'Office_Address as office_address',
                'Wing as wing',
                's_no',
            ]);

        if ($wing) {
            $baseQuery->where('Wing', $wing);
        }
        if ($qText) {
            $baseQuery->where(function ($sub) use ($qText) {
                $sub->where('Name', 'like', '%' . $qText . '%')
                    ->orWhere('Designation', 'like', '%' . $qText . '%')
                    ->orWhere('Office_Address', 'like', '%' . $qText . '%');
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
