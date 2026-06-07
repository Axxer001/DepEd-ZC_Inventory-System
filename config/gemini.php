<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Gemini API Key
    |--------------------------------------------------------------------------
    | Bind via GEMINI_API_KEY in .env — sourced from Google AI Studio.
    */
    'api_key' => env('GEMINI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Gemini Base URL
    |--------------------------------------------------------------------------
    | Default v1beta endpoint. Override via GEMINI_BASE_URL if needed.
    */
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    | Free-tier compatible model. Override via GEMINI_MODEL in .env.
    | Options: gemini-2.0-flash, gemini-2.0-flash-lite, gemini-1.5-flash
    */
    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    | Max seconds to wait per API call. Default 45s for batch sanitation.
    */
    'request_timeout' => env('GEMINI_REQUEST_TIMEOUT', 45),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    | max_attempts: how many times to retry on 429/5xx before giving up.
    | retry_delay_ms: base delay in milliseconds between attempts.
    */
    'max_attempts'   => (int) env('GEMINI_MAX_ATTEMPTS', 3),
    'retry_delay_ms' => (int) env('GEMINI_RETRY_DELAY_MS', 1500),

    /*
    |--------------------------------------------------------------------------
    | Row Chunk Size
    |--------------------------------------------------------------------------
    | Max rows per Gemini call. Kept low to protect token limits and
    | the 4GB local VRAM constraint.
    */
    'chunk_size' => (int) env('GEMINI_CHUNK_SIZE', 40),
];
