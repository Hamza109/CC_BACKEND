<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIController extends Controller
{
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|string',
            'messages.*.content' => 'required|string',
            'model' => 'sometimes|string',
            'temperature' => 'sometimes|numeric',
        ]);

        $model = $validated['model'] ?? 'gpt-4o-mini';
        $temperature = $validated['temperature'] ?? 0.7;

        $refusalMessage = "Sorry, I can only provide information about legal rights and related matters.";

        $systemPrompt = "You are a legal information assistant. You only answer questions related to legal rights, laws, regulations, courts, legal procedures, or access to justice. If a user asks about anything unrelated to law, legal rights, or regulations, you must politely respond with: 'Sorry, I can only provide information about legal rights and related matters.' Keep answers concise and cite jurisdictions if relevant.";

        $messages = $validated['messages'];
        $first = $messages[0] ?? null;
        if (!$first || ($first['role'] ?? null) !== 'system') {
            array_unshift($messages, [
                'role' => 'system',
                'content' => $systemPrompt,
            ]);
        }

        $apiKey = config('openai.api_key');
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'OpenAI API key is not configured. Set OPENAI_API_KEY in .env',
            ], 500);
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to contact OpenAI',
                'details' => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 502);
        }

        $assistantMessage = $response->choices[0]->message ?? null;

        if (!$assistantMessage || !isset($assistantMessage->content) || $assistantMessage->content === null) {
            return response()->json([
                'id' => $response->id,
                'model' => $response->model,
                'usage' => $response->usage,
                'message' => [
                    'role' => 'assistant',
                    'content' => $refusalMessage,
                ],
            ]);
        }

        return response()->json([
            'id' => $response->id,
            'model' => $response->model,
            'usage' => $response->usage,
            'message' => $assistantMessage,
        ]);
    }
}
