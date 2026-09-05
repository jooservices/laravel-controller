<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\OpenApi;

/**
 * Stable schema names, content types, and profile ids for OpenAPI / client codegen.
 * Schemas live in resources/openapi/envelope.v4.yaml — $ref from your app OpenAPI.
 */
final class EnvelopeContract
{
    public const PROFILE_ENVELOPE = 'envelope';

    public const PROFILE_PROBLEM_JSON = 'problem+json';

    public const CONTENT_TYPE_JSON = 'application/json';

    public const CONTENT_TYPE_PROBLEM_JSON = 'application/problem+json';

    public const SCHEMA_API_SUCCESS_ENVELOPE = 'ApiSuccessEnvelope';

    public const SCHEMA_API_ERROR_ENVELOPE = 'ApiErrorEnvelope';

    public const SCHEMA_API_PROBLEM_DETAILS = 'ApiProblemDetails';

    public const SCHEMA_PAGINATION_META = 'PaginationMeta';

    public const SCHEMA_CURSOR_PAGINATION_META = 'CursorPaginationMeta';

    public const SCHEMA_OFFSET_PAGINATION_META = 'OffsetPaginationMeta';

    public const OPENAPI_RELATIVE_PATH = 'resources/openapi/envelope.v4.yaml';

    private function __construct()
    {
    }
}
