<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/contacts",
     *   summary="Create a contact entry",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","email","mobile_no","present_state","present_district","description","category"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="mobile_no", type="string"),
     *       @OA\Property(property="present_state", type="string"),
     *       @OA\Property(property="present_district", type="string"),
     *       @OA\Property(property="description", type="string"),
     *       @OA\Property(property="category", type="string"),
     *       @OA\Property(property="status", type="string"),
     *       @OA\Property(property="comment", type="string")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile_no' => ['required', 'string', 'max:20'],
            'present_state' => ['required', 'string', 'max:255'],
            'present_district' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
        ]);

        if (!isset($data['status']) || $data['status'] === '') {
            $data['status'] = 'Pending';
        }

        // Generate unique reg_no: ENS + yyyymmddHHMM + 4 random digits
        $prefix = 'ENS';
        $datePart = now()->format('YmdHi');
        $attempts = 0;
        do {
            $randomPart = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $regNo = $prefix . $datePart . $randomPart;
            $exists = Contact::where('reg_no', $regNo)->exists();
            $attempts++;
        } while ($exists && $attempts < 5);

        if ($exists) {
            // Extremely unlikely fallback with seconds to break ties
            $regNo = $prefix . now()->format('YmdHis') . str_pad((string)random_int(0, 99), 2, '0', STR_PAD_LEFT);
        }

        $data['reg_no'] = $regNo;

        $contact = Contact::create($data);

        return response()->json($contact, 201);
    }
}
