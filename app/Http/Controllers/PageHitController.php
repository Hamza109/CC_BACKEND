<?php

namespace App\Http\Controllers;

use App\Models\PageHit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class PageHitController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/page-hits",
     *   summary="Store a page hit entry",
     *   tags={"Analytics"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"page_name"},
     *       @OA\Property(property="page_name", type="string", example="home"),
     *       @OA\Property(property="ip_address", type="string", example="192.168.1.10"),
     *       @OA\Property(property="browser", type="string", example="Chrome 118"),
     *       @OA\Property(property="latitude", type="number", format="float", example=34.0837),
     *       @OA\Property(property="longitude", type="number", format="float", example=74.7973),
     *       @OA\Property(property="district", type="string", example="Srinagar"),
     *       @OA\Property(property="state", type="string", example="Jammu & Kashmir"),
     *       @OA\Property(property="country", type="string", example="India"),
     *       @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Page hit stored successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="data", type="object")
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="errors", type="object")
     *     )
     *   )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_name' => ['required', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'browser' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'district' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'created_at' => ['nullable', 'date'],
        ]);

        if (empty($validated['created_at'])) {
            $validated['created_at'] = now();
        }

        $pageHit = PageHit::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $pageHit,
        ], 201);
    }
}


