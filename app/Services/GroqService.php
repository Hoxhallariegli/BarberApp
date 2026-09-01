<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY', '');
    }

    public function interpretPrompt(string $prompt): array
    {
        if (!$this->apiKey) {
            throw new \Exception('GROQ_API_KEY is not set in .env.');
        }

        $systemPrompt = "You are an expert Laravel system architect. Your task is to parse user requests into a valid JSON array of module actions.

        INTENTS:
        - If the user wants to build/create: intent = 'create'.
        - If the user wants to remove/delete/undo: intent = 'delete'.

        STRICT RULES:
        1. MODEL NAME: Must be StudlyCase. Rename reserved words (Class -> SchoolClass, Parent -> Guardian, If -> IfStatement, Else -> ElseStatement).
        2. ICONS: Use short Heroicon v2 names.
           Common valid names: user, users, academic-cap, building-office, building-library, book-open, circle-stack, adjustment-horizontal, bell, calendar, chart-bar, chat-bubble-left-right, clipboard-document, credit-card, envelope, folder, home, identification, newspaper, square-3-stack-3d, tag, ticket, wrench-screwdriver, truck, document, archive-box, pencil-square.
           NEVER use 'book' (use 'book-open'), 'desktop' (use 'computer-desktop'), 'file' (use 'document'), 'office-building' (use 'building-office'), 'box' (use 'archive-box'), 'clipboard' (use 'clipboard-document'), 'pencil' (use 'pencil-square').
        3. FIELDS: Each field must have:
           - name: snake_case.
           - type: MUST be one of [string, text, integer, bigInteger, boolean, decimal, date, datetime, foreignId, enum].
           - constrained: table name for foreignId (e.g., 'users'). MUST BE STRING, NOT NUMBER.
           - nullable: true|false.
           IMPORTANT: 'file', 'image', 'upload' are NOT supported types. Use 'string' instead for paths.

        Structure:
        [
          {
            \"intent\": \"create|delete\",
            \"model\": \"ModelName\",
            \"fields\": [...],
            \"api\": true,
            \"icon\": \"...\"
          }
        ]

        RETURN ONLY RAW JSON.";

        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl, [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.1,
            ]);

        $content = $response->json('choices.0.message.content');
        if (preg_match('/(\[.*\]|\{.*\})/s', $content, $matches)) {
            $content = $matches[0];
        }

        $data = json_decode($content, true);

        if (is_array($data) && isset($data['model'])) {
            return [$data];
        }

        return $data ?? [];
    }
}
