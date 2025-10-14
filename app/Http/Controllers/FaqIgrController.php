<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class FaqIgrController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/faq-igr",
     *   summary="List FAQ IGR (paginated)",
     *   tags={"FAQ IGR"},
     *   @OA\Parameter(name="q", in="query", description="Search in title or content", @OA\Schema(type="string")),
     *   @OA\Parameter(name="category", in="query", description="Filter by category", @OA\Schema(type="string")),
     *   @OA\Parameter(name="page", in="query", description="Page number (default 1)", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="per_page", in="query", description="Items per page (default 20, max 100)", @OA\Schema(type="integer", minimum=1, maximum=100)),
     *   @OA\Response(
     *     response=200,
     *     description="Paginated FAQs",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="data", type="array", @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="q_id", type="integer"),
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="content", type="string"),
     *         @OA\Property(property="category", type="string", nullable=true)
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
        $category = $request->query('category');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $baseQuery = DB::table('citizen.faq_igr')
            ->select(['id', 'q_id', 'title', 'content', 'category']);

        if ($category) {
            $baseQuery->where('category', $category);
        }
        if ($qText) {
            $baseQuery->where(function ($sub) use ($qText) {
                $sub->where('title', 'like', '%' . $qText . '%')
                    ->orWhere('content', 'like', '%' . $qText . '%');
            });
        }

        $countQuery = clone $baseQuery;
        $total = (int) $countQuery->count();

        $data = $baseQuery
            ->orderBy('id')
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
}
