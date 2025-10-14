<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\District;
use Illuminate\Http\Request;

class LocationsController extends Controller
{
    /**
     * @OA\Get(path="/api/states", summary="List states", @OA\Response(response=200, description="OK"))
     */
    public function states()
    {
        return response()->json(State::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get());
    }

    /**
     * @OA\Get(
     *   path="/api/districts-by-state",
     *   summary="List districts (optionally filtered by state_id)",
     *   @OA\Parameter(name="state_id", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function districts(Request $request)
    {
        $stateId = $request->query('state_id');
        $query = District::query();
        if ($stateId !== null && $stateId !== '') {
            $query->where('state_id', $stateId);
        }
        $districts = $query
            ->select(['id', 'state_id', 'name'])
            ->orderBy('name')
            ->get();

        return response()->json($districts);
    }
}
