<?php

namespace App\Http\Controllers;

use App\Models\Scheme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchemeController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/schemes",
     *   summary="List legal aid schemes",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request): JsonResource
    {
        $schemes = Scheme::query()
            ->select(['scheme_id', 'title', 'description', 'file_path', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return JsonResource::collection($schemes);
    }

    /**
     * Serve the scheme PDF by id using its file_path.
     */
    /**
     * @OA\Get(
     *   path="/api/schemes/{id}/file",
     *   summary="Download scheme PDF",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function file(int $id)
    {
        $scheme = Scheme::query()->findOrFail($id);
        $filePath = $scheme->file_path;
    
        if (!$filePath) {
            abort(404, 'File not available');
        }
    
        // Redirect if external URL
        if (preg_match('/^https?:\/\//i', $filePath)) {
            return redirect()->away($filePath);
        }
    
        // Prevent directory traversal
        if (str_contains($filePath, '../') || str_contains($filePath, '..\\')) {
            abort(400, 'Invalid file path');
        }
    
        // Try common Laravel disks
        foreach (['public', 'local'] as $disk) {
            if (Storage::disk($disk)->exists($filePath)) {
                $path = Storage::disk($disk)->path($filePath);
                return response()->file($path, [
                    'Content-Type' => Storage::disk($disk)->mimeType($filePath) ?? 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
                ]);
            }
        }
    
        // Fallback to absolute path
        if (is_file($filePath)) {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            ]);
        }
    
        abort(404, 'File not found');
    }
    
}
