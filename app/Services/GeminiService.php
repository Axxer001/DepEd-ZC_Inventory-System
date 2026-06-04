<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiService - High-density wrapper for Google Gemini API integration.
 * Optimized for resource-constrained environments (4GB VRAM/RAM).
 */
class GeminiService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model = 'gemini-3-flash-preview';

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key') ?? env('GEMINI_API_KEY', '');
        $this->baseUrl = config('gemini.base_url') ?? 'https://generativelanguage.googleapis.com/v1beta';
    }

    /**
     * Execute a generative request to Gemini.
     */
    protected function generate(string $prompt, bool $jsonMode = true): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('[Gemini] API Key missing from configuration.');
            return null;
        }

        $endpoint = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(config('gemini.request_timeout', 30))
                ->post($endpoint, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'responseMimeType' => $jsonMode ? 'application/json' : 'text/plain',
                    ]
                ]);

            if ($response->failed()) {
                $this->handleFailure($response);
                return null;
            }

            return $this->parseResponse($response);
        } catch (\Exception $e) {
            Log::critical("[Gemini] Connection failure: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Sanitize and type-detect unstructured spreadsheet rows in bulk.
     */
    public function sanitizeRows(array $rows, string $schemaType): array
    {
        if (empty($rows)) return [];

        // Chunking at 50 rows to respect token limits and protect local memory.
        return collect($rows)->chunk(50)->flatMap(function ($chunk) use ($schemaType) {
            $prompt = $this->buildSanitationPrompt($chunk->toArray(), $schemaType);
            $result = $this->generate($prompt);

            return $result['rows'] ?? $result['data'] ?? (is_array($result) && array_is_list($result) ? $result : $chunk->toArray());
        })->toArray();
    }

    /**
     * Build strict architectural prompts for data sanitation.
     */
    private function buildSanitationPrompt(array $data, string $type): string
    {
        $json = json_encode($data);
        $rules = match ($type) {
            'buildings' => "
                1. 'storeys', 'classrooms': Strict integer or null.
                2. 'date_constructed', 'acquisition_date': ISO-8601 (YYYY-MM-DD). Default to 01-01 if day/month missing.
                3. 'acquisition_cost', 'appraised_value': Float. Remove '₱', commas, and whitespace.
                4. Trim and Title Case: 'office_name', 'classification', 'article', 'description'.",
            'assets' => "
                1. 'acquisition_date': ISO-8601 (YYYY-MM-DD).
                2. 'acquisition_cost': Float. No symbols.
                3. Standardize: 'classification' (Semi-Expendable/Non-Expendable), 'article'.
                4. Trim: 'description', 'property_number', 'office_name'.",
            'manual_batch' => "
                1. 'classification', 'category', 'item', 'mode': Standardize nomenclature.
                2. 'custodian-first', 'custodian-middle', 'custodian-last': Clean name components.
                3. 'cost', 'qty', 'useful-life': Strict numeric types.
                4. 'acceptance-date', 'acquisition-date': YYYY-MM-DD.",
            default => "Clean and trim all string values. Ensure numeric fields are typed correctly."
        };

        return "Act as a senior data architect. Sanitize the following JSON array based on these strict rules: {$rules}\n\nDATA: {$json}\n\nReturn ONLY the sanitized JSON array under a 'rows' key.";
    }

    private function parseResponse(Response $response): ?array
    {
        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) return null;

        return json_decode($text, true);
    }

    private function handleFailure(Response $response): void
    {
        Log::error("[Gemini] API Error: {$response->status()} - " . $response->body());
    }
}
