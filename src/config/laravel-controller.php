<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Response Formatter
    |--------------------------------------------------------------------------
    |
    | Provide a class name that implements
    | JOOservices\LaravelController\Contracts\ResponseFormatter to fully
    | control the final JSON envelope. When null, the package uses its default
    | standardized response shape and key mapping.
    |
    */
    'response_formatter' => null,

    'keys' => [
        'success' => 'success',
        'code' => 'code',
        'message' => 'message',
        'data' => 'data',
        'errors' => 'errors',
        'meta' => 'meta',
        'trace_id' => 'trace_id',
        'warnings' => 'warnings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Use Localized Messages
    |--------------------------------------------------------------------------
    |
    | When true, default messages (e.g. "Not Found", "Unauthorized") are
    | resolved via __() using the laravel-controller::messages.* keys.
    | Publish lang files with: --tag=laravel-controller-lang
    |
    */
    'use_translations' => false,

    /*
    |--------------------------------------------------------------------------
    | Trace ID
    |--------------------------------------------------------------------------
    |
    | The package reads this request header before generating a fallback UUID.
    | Keep the default unless your edge/API gateway already emits a different
    | correlation header.
    |
    */
    'trace_id' => [
        'header' => 'X-Trace-ID',
    ],

    /*
    |--------------------------------------------------------------------------
    | 204 No Content Envelope
    |--------------------------------------------------------------------------
    |
    | When true, noContent() returns the same envelope as other responses
    | (success, code, message, data: null, meta, trace_id) so clients
    | always receive a consistent top-level shape. When false, 204 returns [].
    |
    */
    'envelope_204' => false,

    /*
    |--------------------------------------------------------------------------
    | Success HTTP Codes
    |--------------------------------------------------------------------------
    |
    | HTTP codes that are considered "success" in the envelope (success: true).
    | Default null means any 2xx is success. Set to e.g. [200, 201] to treat
    | only those as success (e.g. 202 Accepted as "success but async").
    |
    */
    'success_codes' => null,

    /*
    |--------------------------------------------------------------------------
    | Validation (422) Message
    |--------------------------------------------------------------------------
    |
    | For ValidationException rendering: "message" sets a fixed string,
    | "first" uses the first validation error message as the top-level message.
    |
    */
    'validation' => [
        'message' => 'Unprocessable Entity', // or 'first' to use first error
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the default routes registered by the package.
    | You can disable them entirely or change the URL prefix.
    |
    */
    'routes' => [
        'enabled' => true,
        'prefix' => 'api/v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Endpoint
    |--------------------------------------------------------------------------
    |
    | Configure what the /status endpoint returns. Set to true to include
    | version, environment, or maintenance flag so clients can adapt (e.g.
    | show "API under maintenance" or "please upgrade client").
    | Optional "checks" run health checks (database, cache, queue) with timeout.
    |
    */
    'status' => [
        'include_version' => true,
        'include_environment' => true,
        'include_maintenance' => true,
        'checks' => [], // e.g. ['database', 'cache', 'queue'] with optional timeout_seconds per check
        'checks_timeout_seconds' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Links
    |--------------------------------------------------------------------------
    |
    | When true, paginated responses include meta.links with first, last, prev,
    | next URLs for HAL-style or link-based navigation.
    |
    */
    'pagination_links' => true,

    /*
    |--------------------------------------------------------------------------
    | Item Links (HAL-style for single resource)
    |--------------------------------------------------------------------------
    |
    | When true, respondWithItem() can accept an optional third argument $links
    | (array). If not provided but item_links_default is set, those links are
    | merged into meta.links for single-item responses (e.g. self, index).
    |
    */
    'item_links' => true,
    'item_links_default' => null, // e.g. ['index' => '/api/v1/users'] or leave null
];
