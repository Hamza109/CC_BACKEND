<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private $chatServiceUrl = 'http://localhost:8002/chat';

    public function chat(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        try {
            // Make HTTP request to the external chat service
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->chatServiceUrl, [
                    'question' => $validated['question']
                ]);

            // Check if the request was successful
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ], $response->status());
            } else {
                // Log the error for debugging
                Log::error('Chat service error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'question' => $validated['question']
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Chat service is currently unavailable',
                    'message' => 'Please try again later',
                ], 502);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors
            Log::error('Chat service connection error', [
                'error' => $e->getMessage(),
                'question' => $validated['question']
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unable to connect to chat service',
                'message' => 'Please try again later',
            ], 503);

        } catch (\Exception $e) {
            // Handle any other errors
            Log::error('Chat service unexpected error', [
                'error' => $e->getMessage(),
                'question' => $validated['question']
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred',
                'message' => 'Please try again later',
            ], 500);
        }
    }
}
