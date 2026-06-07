<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiService — High-density HTTP wrapper for Google Gemini API.
 *
 * Design goals:
 *  - All knobs (model, timeout, retry, chunk size) driven by config/gemini.php.
 *  - Exponential backoff on 429 / 5xx to respect the free-tier 1 000 req/day quota.
 *  - Chunk rows at configurable size to stay inside token limits and protect
 *    the 4 GB VRAM local constraint.
 *  - Hard fallback: if Gemini is unavailable, return the original data untouched
 *    so the pipeline never silently drops records.
 */
class GeminiService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private int    $timeout;
    private int    $maxAttempts;
    private int    $retryDelayMs;
    private int    $chunkSize;

    public function __construct()
    {
        $this->apiKey       = config('gemini.api_key', '');
        $this->baseUrl      = rtrim(config('gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->model        = config('gemini.model', 'gemini-2.0-flash');
        $this->timeout      = (int) config('gemini.request_timeout', 45);
        $this->maxAttempts  = (int) config('gemini.max_attempts', 3);
        $this->retryDelayMs = (int) config('gemini.retry_delay_ms', 1500);
        $this->chunkSize    = (int) config('gemini.chunk_size', 40);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Sanitize and type-detect unstructured spreadsheet rows in bulk.
     * Rows are chunked at $chunkSize to respect token limits.
     * Falls back to original chunk on API failure — never drops records.
     */
    public function sanitizeRows(array $rows, string $schemaType): array
    {
        if (empty($rows)) return [];

        return collect($rows)
            ->chunk($this->chunkSize)
            ->flatMap(function ($chunk) use ($schemaType) {
                $original = $chunk->toArray();
                $prompt   = $this->buildSanitationPrompt($original, $schemaType);
                $result   = $this->generate($prompt, jsonMode: true);

                // Unwrap response: accept { "rows": [...] } or a bare array
                $sanitized = $result['rows'] ?? $result['data']
                    ?? (is_array($result) && array_is_list($result) ? $result : null);

                // Hard fallback — return original if Gemini fails or returns garbage
                return $sanitized ?? $original;
            })
            ->toArray();
    }

    /**
     * AI-based spreadsheet template-type detection.
     * Returns 'buildings' | 'assets' | 'unknown'.
     */
    public function detectTemplateType(array $headers): string
    {
        if (empty($headers)) return 'unknown';

        $prompt = $this->buildTypeDetectionPrompt($headers);
        $result = $this->generate($prompt, jsonMode: true);

        $type = strtolower(trim($result['type'] ?? $result['template_type'] ?? 'unknown'));

        return in_array($type, ['buildings', 'assets'], strict: true) ? $type : 'unknown';
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PROMPT BUILDERS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Build strict, schema-aware sanitation prompts per data type.
     */
    private function buildSanitationPrompt(array $data, string $type): string
    {
        $json  = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $rules = match ($type) {
            'buildings' => "
                1. 'storeys', 'classrooms': Strict integer or null.
                2. 'date_constructed', 'acquisition_date', 'appraisal_date': ISO-8601 (YYYY-MM-DD). Default day/month to 01 if missing.
                3. 'acquisition_cost', 'appraised_value': Float. Strip ₱, commas, whitespace.
                4. 'estimated_useful_life': Integer. Default to 25 if empty.
                5. Trim and Title Case: 'office_name', 'classification', 'article', 'description'.
                6. 'region': Keep as-is (e.g. 'REGION IX'). 'division': Keep as-is.",

            'assets' => "
                1. 'acquisition_date': ISO-8601 (YYYY-MM-DD).
                2. 'acquisition_cost', 'unit_value', 'total_value': Float. Strip ₱, commas, whitespace.
                3. 'quantity': Strict integer, minimum 1.
                4. 'classification': Standardize to 'Semi-Expendable' or 'Non-Expendable' where detectable.
                5. Trim: 'article', 'description', 'property_number', 'office_name', 'remarks'.",

            'manual_batch' => "
                1. 'classification', 'category', 'item': Standardize and Title Case nomenclature.
                2. 'mode': Standardize procurement mode strings (e.g. 'Direct Contracting', 'Shopping').
                3. Employee fields ('employee-first', 'employee-middle', 'employee-last'): Clean name components, Proper Case.
                4. 'cost': Float, strip currency symbols. 'qty': Integer. 'useful-life': Integer.
                5. 'acceptance-date', 'acquisition-date': Strict YYYY-MM-DD.
                6. 'condition': Map to exactly one of: 'Good Condition', 'Needs Repair', 'Unserviceable'.",

            default => "Clean and trim all string values. Ensure numeric fields are correctly typed as numbers.",
        };

        return "You are a senior data architect. Sanitize the following JSON array strictly per these rules:\n\n"
            . "RULES:\n{$rules}\n\n"
            . "DATA:\n{$json}\n\n"
            . "Return ONLY a valid JSON object with a single 'rows' key containing the sanitized array. "
            . "Do not add explanations or markdown fences.";
    }

    /**
     * Prompt for AI-based spreadsheet template type detection.
     */
    private function buildTypeDetectionPrompt(array $headers): string
    {
        $headerList = implode(', ', array_filter($headers));

        return "You are a document type classifier for Philippine government property inventory spreadsheets.\n\n"
            . "Given these column headers from row 8 of a spreadsheet:\n\"{$headerList}\"\n\n"
            . "Classify the template type:\n"
            . "- 'buildings' if headers relate to structures (storeys, classrooms, date constructed, school address).\n"
            . "- 'assets'    if headers relate to movable property (article, item description, property number, unit value).\n"
            . "- 'unknown'   if neither pattern matches.\n\n"
            . "Return ONLY: { \"type\": \"buildings\" | \"assets\" | \"unknown\" }";
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HTTP TRANSPORT — WITH EXPONENTIAL BACKOFF
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Execute a generative request to Gemini with retry/backoff.
     * Returns the decoded JSON payload or null on hard failure.
     */
    protected function generate(string $prompt, bool $jsonMode = true): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('[Gemini] API key missing. Set GEMINI_API_KEY in .env');
            return null;
        }

        $endpoint = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";
        $body     = [
            'contents'         => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'responseMimeType' => $jsonMode ? 'application/json' : 'text/plain',
                'temperature'      => 0.1,   // near-deterministic for data cleaning
            ],
        ];

        $attempt = 0;
        $delay   = $this->retryDelayMs;

        while ($attempt < $this->maxAttempts) {
            $attempt++;
            try {
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout($this->timeout)
                    ->post($endpoint, $body);

                if ($response->successful()) {
                    return $this->parseResponse($response);
                }

                // Rate-limited or server error — back off and retry
                if (in_array($response->status(), [429, 500, 502, 503], strict: true)) {
                    Log::warning("[Gemini] HTTP {$response->status()} on attempt {$attempt}/{$this->maxAttempts}. Retrying in {$delay}ms.");
                    usleep($delay * 1000);
                    $delay *= 2; // exponential backoff
                    continue;
                }

                // 4xx non-retryable
                $this->handleFailure($response, $attempt);
                return null;

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning("[Gemini] Connection error on attempt {$attempt}: {$e->getMessage()}");
                usleep($delay * 1000);
                $delay *= 2;
            } catch (\Exception $e) {
                Log::critical("[Gemini] Unexpected failure: {$e->getMessage()}");
                return null;
            }
        }

        Log::error("[Gemini] All {$this->maxAttempts} attempts exhausted. Falling back to original data.");
        return null;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // RESPONSE PARSING
    // ═══════════════════════════════════════════════════════════════════════

    private function parseResponse(Response $response): ?array
    {
        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            Log::warning('[Gemini] Empty content in API response.', ['status' => $response->status()]);
            return null;
        }

        // Strip markdown fences if the model wraps the JSON anyway
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);

        $decoded = json_decode($text, associative: true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('[Gemini] JSON decode failed: ' . json_last_error_msg(), ['raw' => substr($text, 0, 300)]);
            return null;
        }

        return $decoded;
    }

    private function handleFailure(Response $response, int $attempt): void
    {
        Log::error("[Gemini] Non-retryable error (attempt {$attempt}): HTTP {$response->status()}", [
            'body' => substr($response->body(), 0, 500),
        ]);
    }
}
