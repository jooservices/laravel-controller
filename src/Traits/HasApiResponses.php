<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Traits;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use InvalidArgumentException;
use JOOservices\LaravelController\Contracts\ResponseFormatter;
use JOOservices\LaravelController\Formatters\ProblemDetailsFormatter;
use JOOservices\LaravelController\OpenApi\EnvelopeContract;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV4;
use UnexpectedValueException;

trait HasApiResponses
{
    protected const MESSAGE_NOT_FOUND = 'Not Found';

    private const MESSAGE_UNPROCESSABLE = 'Unprocessable Entity';

    private const MESSAGE_TOO_MANY_REQUESTS = 'Too Many Requests';

    /**
     * Return a success response.
     *
     * @param  array<string, mixed>  $meta
     * @param  array<int, string>|array<string, string>  $warnings  Non-fatal warnings.
     * @throws UnexpectedValueException
     */
    public function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
        array $meta = [],
        array $warnings = [],
    ): JsonResponse {
        return $this->formatResponse(true, $code, $message, $data, null, $meta, $warnings);
    }

    /**
     * Return an error response.
     * @throws UnexpectedValueException
     */
    public function error(string $message, int $code = Response::HTTP_BAD_REQUEST, mixed $errors = null): JsonResponse
    {
        return $this->formatResponse(false, $code, $message, null, $errors);
    }

    /**
     * Laravel-friendly alias for returning normalized payload data.
     *
     * @param  array<string, mixed>  $meta
     * @param  array<int, string>|array<string, string>  $warnings
     * @throws UnexpectedValueException
     */
    public function respondWithData(
        mixed $data = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
        array $meta = [],
        array $warnings = [],
    ): JsonResponse {
        return $this->success($data, $message, $code, $meta, $warnings);
    }

    /**
     * Laravel-friendly alias for returning an API error envelope.
     * @throws UnexpectedValueException
     */
    public function respondWithError(
        string $message,
        int $code = Response::HTTP_BAD_REQUEST,
        mixed $errors = null,
    ): JsonResponse {
        return $this->error($message, $code, $errors);
    }

    /**
     * Return an RFC 7807 Problem Details response (application/problem+json).
     *
     * @throws UnexpectedValueException
     */
    public function respondWithProblem(
        string $title,
        int $status,
        ?string $detail = null,
        mixed $errors = null,
        ?string $type = null,
    ): JsonResponse {
        $type ??= 'https://jooservices.dev/problems/http-' . $status;

        $meta = [
            'problem_type' => $type,
        ];

        if ($detail !== null && $detail !== '') {
            $meta['problem_detail'] = $detail;
        }

        $instance = request()->getRequestUri();
        if (is_string($instance) && $instance !== '') {
            $meta['problem_instance'] = $instance;
        }

        $previousFormatter = config('laravel-controller.response_formatter');
        $previousProfile = config('laravel-controller.response_profile');

        config([
            'laravel-controller.response_formatter' => ProblemDetailsFormatter::class,
            'laravel-controller.response_profile' => EnvelopeContract::PROFILE_PROBLEM_JSON,
        ]);

        try {
            return $this->formatResponse(false, $status, $title, null, $errors, $meta);
        } finally {
            config([
                'laravel-controller.response_formatter' => $previousFormatter,
                'laravel-controller.response_profile' => $previousProfile,
            ]);
        }
    }

    /**
     * Laravel-friendly alias for a 204 response.
     *
     * @throws InvalidArgumentException
     */
    public function respondNoContent(): JsonResponse
    {
        return $this->noContent();
    }

    /**
     * Return a created response (201).
     * @throws UnexpectedValueException
     */
    public function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Return a no content response (204).
     * RFC 9110: 204 responses must not include a content body.
     *
     * @throws InvalidArgumentException
     */
    public function noContent(): JsonResponse
    {
        $response = new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
        $response->setContent('');

        return $response;
    }

    /**
     * Return an accepted response (202). Use for async operations (e.g. "request accepted, processing").
     * @throws UnexpectedValueException
     */
    public function accepted(mixed $data = null, string $message = 'Accepted'): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_ACCEPTED);
    }

    /**
     * Return a conflict response (409). Use for duplicate resource or version conflicts.
     * @throws UnexpectedValueException
     */
    public function conflict(string $message = 'Conflict', mixed $errors = null): JsonResponse
    {
        return $this->error($message, Response::HTTP_CONFLICT, $errors);
    }

    /**
     * Return a gone response (410). Use for deprecated or permanently removed resources.
     * @throws UnexpectedValueException
     */
    public function gone(string $message = 'Gone'): JsonResponse
    {
        return $this->error($message, Response::HTTP_GONE);
    }

    /**
     * Return a bad request response (400).
     * @throws UnexpectedValueException
     */
    public function badRequest(string $message = 'Bad Request', mixed $errors = null): JsonResponse
    {
        return $this->error($message, Response::HTTP_BAD_REQUEST, $errors);
    }

    /**
     * Return an unauthorized response (401).
     * @throws UnexpectedValueException
     */
    public function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        $msg = $message === 'Unauthorized' ? $this->trans('Unauthorized', 'unauthorized') : $message;

        return $this->error($msg, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Return a forbidden response (403).
     * @throws UnexpectedValueException
     */
    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        $msg = $message === 'Forbidden' ? $this->trans('Forbidden', 'forbidden') : $message;

        return $this->error($msg, Response::HTTP_FORBIDDEN);
    }

    /**
     * Return a not found response (404).
     * @throws UnexpectedValueException
     */
    public function notFound(string $message = self::MESSAGE_NOT_FOUND): JsonResponse
    {
        $msg = $message === self::MESSAGE_NOT_FOUND
            ? $this->trans(self::MESSAGE_NOT_FOUND, 'not_found')
            : $message;

        return $this->error($msg, Response::HTTP_NOT_FOUND);
    }

    /**
     * Return an unprocessable entity response (422). Validation errors go in $errors.
     * For backward compatibility, the first argument may be an array of errors (message then defaults).
     *
     * @param  string|array<string, array<int, string>>  $messageOrErrors
     * @throws UnexpectedValueException
     */
    public function unprocessable(
        string | array $messageOrErrors = self::MESSAGE_UNPROCESSABLE,
        mixed $errors = null,
    ): JsonResponse {
        if (is_array($messageOrErrors)) {
            $errors = $messageOrErrors;
            $messageOrErrors = self::MESSAGE_UNPROCESSABLE;
        }
        $msg = $messageOrErrors === self::MESSAGE_UNPROCESSABLE
            ? $this->trans(self::MESSAGE_UNPROCESSABLE, 'unprocessable')
            : $messageOrErrors;

        return $this->error($msg, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Return an internal server error response (500).
     * @throws UnexpectedValueException
     */
    public function internalError(string $message = 'Internal Server Error'): JsonResponse
    {
        $default = 'Internal Server Error';
        $msg = $message === $default ? $this->trans($default, 'internal_error') : $message;

        return $this->error($msg, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Translate message when use_translations is enabled; otherwise return as-is.
     *
     * @param  string  $default  Fallback when translation key is missing.
     * @param  string  $key  Key under laravel-controller::messages.* (e.g. not_found, unauthorized).
     */
    protected function trans(string $default, string $key = 'message'): string
    {
        if (config('laravel-controller.use_translations', false) !== true) {
            return $default;
        }

        $translated = __("laravel-controller::messages.{$key}");
        $keyLiteral = "laravel-controller::messages.{$key}";

        return (is_string($translated) && $translated !== $keyLiteral) ? $translated : $default;
    }

    /**
     * Format the response structure.
     *
     * @param  array<string, mixed>  $meta
     * @param  array<int, string>|array<string, string>  $warnings
     *
     * @throws UnexpectedValueException
     */
    protected function formatResponse(
        bool $success,
        int $code,
        string $message,
        mixed $data = null,
        mixed $errors = null,
        array $meta = [],
        array $warnings = [],
    ): JsonResponse {
        [$data, $meta] = $this->resolveResourcePayload($data, $meta);
        $data = $this->normalizeResponseValue($data);
        $errors = $this->normalizeResponseValue($errors);
        $meta = $this->normalizeStringKeyedArray($meta);
        $meta = $this->mergeMetaHeaders($meta);

        $effectiveSuccess = $this->isEffectiveSuccess($success, $code);

        $payload = $this->resolveResponsePayload([
            'success' => $effectiveSuccess,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
            'meta' => $meta,
            'warnings' => $this->normalizeWarnings($warnings),
            'trace_id' => $this->resolveTraceId(),
            'keys' => $this->configuredResponseKeys(),
        ]);

        $response = response()->json($payload, $code);

        if (! $effectiveSuccess && $this->usesProblemJsonProfile()) {
            $response->headers->set('Content-Type', EnvelopeContract::CONTENT_TYPE_PROBLEM_JSON);
        }

        return $response;
    }

    /**
     * Echo configured request headers into meta when present.
     *
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected function mergeMetaHeaders(array $meta): array
    {
        $config = config('laravel-controller.meta_headers');

        if (! is_array($config) || ($config['enabled'] ?? true) !== true) {
            return $meta;
        }

        $meta = $this->mergeIdempotencyMetaHeader($meta, $config);
        $meta = $this->mergeRateLimitMetaHeaders($meta, $config);
        $meta = $this->mergeRetryAfterMetaHeader($meta, $config);

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<mixed>  $config
     * @return array<string, mixed>
     */
    protected function mergeIdempotencyMetaHeader(array $meta, array $config): array
    {
        $idempotencyHeader = $config['idempotency'] ?? 'Idempotency-Key';
        if (! is_string($idempotencyHeader) || $idempotencyHeader === '') {
            return $meta;
        }

        $idempotencyKey = request()->header($idempotencyHeader);
        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $meta['idempotency_key'] = $idempotencyKey;
        }

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<mixed>  $config
     * @return array<string, mixed>
     */
    protected function mergeRateLimitMetaHeaders(array $meta, array $config): array
    {
        $rateLimit = $config['rate_limit'] ?? [];
        if (! is_array($rateLimit)) {
            return $meta;
        }

        $rateMeta = [];
        foreach (['limit', 'remaining', 'reset'] as $field) {
            $headerName = $rateLimit[$field] ?? null;
            if (! is_string($headerName) || $headerName === '') {
                continue;
            }
            $value = request()->header($headerName);
            if (is_string($value) && $value !== '') {
                $rateMeta[$field] = $value;
            }
        }

        if ($rateMeta !== []) {
            $meta['rate_limit'] = $rateMeta;
        }

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<mixed>  $config
     * @return array<string, mixed>
     */
    protected function mergeRetryAfterMetaHeader(array $meta, array $config): array
    {
        $retryAfterHeader = $config['retry_after'] ?? 'Retry-After';
        if (! is_string($retryAfterHeader) || $retryAfterHeader === '') {
            return $meta;
        }

        $retryAfter = request()->header($retryAfterHeader);
        if (is_string($retryAfter) && $retryAfter !== '') {
            $meta['retry_after'] = $retryAfter;
        }

        return $meta;
    }

    protected function usesProblemJsonProfile(): bool
    {
        $formatterClass = config('laravel-controller.response_formatter');
        if (is_string($formatterClass) && $formatterClass === ProblemDetailsFormatter::class) {
            return true;
        }

        $profile = config('laravel-controller.response_profile', EnvelopeContract::PROFILE_ENVELOPE);

        return $profile === EnvelopeContract::PROFILE_PROBLEM_JSON;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array{0: mixed, 1: array<string, mixed>}
     */
    protected function resolveResourcePayload(mixed $data, array $meta): array
    {
        if ($data instanceof ResourceCollection) {
            /** @var array<string, mixed>|list<mixed> $response */
            $response = $data->response()->getData(true);

            if ($this->isLaravelResourceEnvelope($response)) {
                /** @var array<string, mixed> $response */
                $data = $response['data'];
                $meta = array_merge($meta, (array) ($response['meta'] ?? []));

                if (isset($response['links']) && is_array($response['links']) && $response['links'] !== []) {
                    $meta['links'] = $response['links'];
                }

                return [$data, $this->normalizeStringKeyedArray($meta)];
            }

            return [$response, $this->normalizeStringKeyedArray($meta)];
        }

        if ($data instanceof JsonResource) {
            $data = $data->resolve();
        }

        return [$data, $this->normalizeStringKeyedArray($meta)];
    }

    /**
     * True when Laravel returned an associative envelope (wrapped or paginated),
     * not a bare list from JsonResource::withoutWrapping().
     *
     * @param  mixed  $response
     */
    protected function isLaravelResourceEnvelope(mixed $response): bool
    {
        return is_array($response)
            && ! array_is_list($response)
            && array_key_exists('data', $response);
    }

    protected function isEffectiveSuccess(bool $success, int $code): bool
    {
        if (! $success) {
            return false;
        }

        $successCodes = config('laravel-controller.success_codes');

        if (is_array($successCodes)) {
            return in_array($code, $successCodes, true);
        }

        return $code >= Response::HTTP_OK && $code < Response::HTTP_MULTIPLE_CHOICES;
    }

    /**
     * @param  string|null  $resourceClass
     *
     * @throws UnexpectedValueException
     */
    protected function assertApiResourceClass(?string $resourceClass): void
    {
        if ($resourceClass === null) {
            return;
        }

        if (! class_exists($resourceClass) || ! is_a($resourceClass, JsonResource::class, true)) {
            throw new UnexpectedValueException(sprintf(
                'API resource class [%s] must exist and extend %s.',
                $resourceClass,
                JsonResource::class,
            ));
        }
    }

    protected function resolveTraceId(): string
    {
        $traceHeader = config('laravel-controller.trace_id.header', 'X-Trace-ID');
        $headerName = 'X-Trace-ID';

        if (is_string($traceHeader) && trim($traceHeader) !== '') {
            $headerName = $traceHeader;
        }

        $traceIdHeader = request()->header($headerName);

        return is_string($traceIdHeader) && $traceIdHeader !== ''
            ? $traceIdHeader
            : (string) new UuidV4();
    }

    /**
     * @return array<string, string>
     */
    protected function configuredResponseKeys(): array
    {
        return array_filter(
            (array) config('laravel-controller.keys', []),
            static fn(mixed $value, mixed $key): bool => is_string($key) && is_string($value),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * Resolve the final JSON payload.
     *
     * @param  array{
     *     success: bool,
     *     code: int,
     *     message: string,
     *     data: mixed,
     *     errors: mixed,
     *     meta: array<string, mixed>,
     *     warnings: array<int, string>|array<string, string>,
     *     trace_id: string,
     *     keys: array<string, string>
     * }  $response
     * @return array<string, mixed>
     *
     * @throws UnexpectedValueException
     */
    protected function resolveResponsePayload(array $response): array
    {
        $formatterClass = config('laravel-controller.response_formatter');

        if (
            (! is_string($formatterClass) || $formatterClass === '')
            && config('laravel-controller.response_profile') === EnvelopeContract::PROFILE_PROBLEM_JSON
        ) {
            $formatterClass = ProblemDetailsFormatter::class;
        }

        if (is_string($formatterClass) && $formatterClass !== '') {
            $formatter = app($formatterClass);

            if (! $formatter instanceof ResponseFormatter) {
                throw new UnexpectedValueException(sprintf(
                    'Configured response formatter [%s] must implement %s.',
                    $formatterClass,
                    ResponseFormatter::class,
                ));
            }

            return $formatter->format($response);
        }

        $payload = [
            $this->responseKey('success') => $response['success'],
            $this->responseKey('code') => $response['code'],
            $this->responseKey('message') => $response['message'],
            $this->responseKey('data') => $response['data'],
            $this->responseKey('meta') => (object) $response['meta'],
            $this->responseKey('errors') => $response['errors'],
            $this->responseKey('trace_id') => $response['trace_id'],
        ];

        if ($response['warnings'] !== []) {
            $payload[$this->responseKey('warnings')] = $response['warnings'];
        }

        return $payload;
    }

    protected function responseKey(string $key): string
    {
        $value = config("laravel-controller.keys.{$key}", $key);

        return is_string($value) && $value !== '' ? $value : $key;
    }

    protected function normalizeResponseValue(mixed $value): mixed
    {
        if ($value instanceof JsonResource) {
            return $this->normalizeResponseValue($value->resolve());
        }

        if ($value instanceof Arrayable) {
            return $this->normalizeResponseValue($value->toArray());
        }

        if ($value instanceof JsonSerializable) {
            return $this->normalizeResponseValue($value->jsonSerialize());
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            /** @var mixed $arrayValue */
            $arrayValue = $value->toArray();

            return $this->normalizeResponseValue($arrayValue);
        }

        if (is_array($value)) {
            return array_map(fn(mixed $item): mixed => $this->normalizeResponseValue($item), $value);
        }

        return $value;
    }

    /**
     * @param  array<mixed>  $items
     * @return array<string, mixed>
     */
    protected function normalizeStringKeyedArray(array $items): array
    {
        $normalized = [];

        foreach ($items as $key => $value) {
            $normalized[(string) $key] = $this->normalizeResponseValue($value);
        }

        return $normalized;
    }

    /**
     * @param  array<int, string>|array<string, string>  $warnings
     * @return array<int, string>|array<string, string>
     */
    protected function normalizeWarnings(array $warnings): array
    {
        $normalized = [];

        foreach ($warnings as $key => $value) {
            if ($value !== '') {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    /**
     * Return a too many requests response (429).
     * Use respondTooManyRequestsFromRequest() to derive retry_after from Laravel's rate limiter.
     * @throws UnexpectedValueException
     */
    public function tooManyRequests(
        string $message = self::MESSAGE_TOO_MANY_REQUESTS,
        int $retryAfter = 60,
    ): JsonResponse {
        $msg = $message === self::MESSAGE_TOO_MANY_REQUESTS
            ? $this->trans(self::MESSAGE_TOO_MANY_REQUESTS, 'too_many_requests')
            : $message;

        return $this->error($msg, Response::HTTP_TOO_MANY_REQUESTS, [
            'retry_after' => $retryAfter,
        ])->withHeaders(['Retry-After' => (string) $retryAfter]);
    }

    /**
     * Return 429 using Retry-After from request (e.g. throttle or custom limiter).
     * Falls back to $defaultRetryAfter seconds when header is missing.
     * @throws UnexpectedValueException
     */
    public function respondTooManyRequestsFromRequest(
        string $message = self::MESSAGE_TOO_MANY_REQUESTS,
        int $defaultRetryAfter = 60,
    ): JsonResponse {
        $retryAfter = (int) request()->header('Retry-After', (string) $defaultRetryAfter);

        return $this->tooManyRequests($message, $retryAfter > 0 ? $retryAfter : $defaultRetryAfter);
    }

    /**
     * Return a paginated response. Use with LengthAwarePaginator; optionally pass a resource class to transform items.
     *
     * @param  string|null  $resourceClass
     * @throws UnexpectedValueException
     */
    public function respondWithPagination(
        mixed $paginator,
        ?string $resourceClass = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
    ): JsonResponse {
        if ($paginator instanceof LengthAwarePaginator) {
            $items = $paginator->items();
            $this->assertApiResourceClass($resourceClass);

            if ($resourceClass !== null) {
                /** @var class-string<JsonResource> $resourceClass */
                $items = $resourceClass::collection($items);
            }

            $meta = [
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'last_page' => $paginator->lastPage(),
                ],
            ];

            if (config('laravel-controller.pagination_links', true) === true) {
                $meta['links'] = [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ];
            }

            return $this->success($items, $message, $code, $meta);
        }

        return $this->success($paginator, $message, $code);
    }

    /**
     * Return a cursor-paginated response.
     * Accepts Laravel CursorPaginator (auto-extracts items/cursors) or an iterable API.
     * Pagination fields are nested under meta.pagination (v4).
     * For the iterable form, has_more is true when next_cursor is non-null.
     *
     * @param  iterable<mixed>|CursorPaginator<int, mixed>  $items
     * @param  string|int|null  $cursor  Current cursor (opaque token or id). Ignored when $items is CursorPaginator.
     * @param  string|int|null  $nextCursor  Cursor for next page, or null if no next page.
     * @param  string|null  $resourceClass
     * @throws UnexpectedValueException
     */
    public function respondWithCursorPagination(
        iterable | CursorPaginator $items,
        mixed $cursor = null,
        mixed $nextCursor = null,
        ?string $resourceClass = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
    ): JsonResponse {
        $prevCursor = null;
        $perPage = null;
        $links = null;
        $hasMore = $nextCursor !== null;

        if ($items instanceof CursorPaginator) {
            $paginator = $items;
            $cursor = $this->encodeCursorValue($paginator->cursor());
            $nextCursor = $this->encodeCursorValue($paginator->nextCursor());
            $prevCursor = $this->encodeCursorValue($paginator->previousCursor());
            $hasMore = $paginator->hasMorePages();
            $perPage = $paginator->perPage();
            $items = $paginator->items();

            if (config('laravel-controller.pagination_links', true) === true) {
                $links = [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ];
            }
        }

        $items = is_array($items) ? $items : iterator_to_array($items);
        $this->assertApiResourceClass($resourceClass);

        if ($resourceClass !== null) {
            /** @var class-string<JsonResource> $resourceClass */
            $items = $resourceClass::collection($items)->resolve();
        }

        $pagination = [
            'cursor' => $cursor,
            'next_cursor' => $nextCursor,
            'has_more' => $hasMore,
        ];

        if ($prevCursor !== null) {
            $pagination['prev_cursor'] = $prevCursor;
        }

        if ($perPage !== null) {
            $pagination['per_page'] = $perPage;
        }

        $meta = [
            'pagination' => $pagination,
        ];

        if (is_array($links)) {
            $meta['links'] = $links;
        }

        return $this->success($items, $message, $code, $meta);
    }

    /**
     * Encode a Laravel Cursor object or return scalar cursors as-is.
     */
    protected function encodeCursorValue(mixed $cursor): string | int | null
    {
        if ($cursor === null) {
            return null;
        }

        if (is_object($cursor) && method_exists($cursor, 'encode')) {
            /** @var mixed $encoded */
            $encoded = $cursor->encode();

            return is_string($encoded) || is_int($encoded) ? $encoded : null;
        }

        if (is_string($cursor) || is_int($cursor)) {
            return $cursor;
        }

        return null;
    }

    /**
     * Return an offset-paginated response (offset/limit style).
     * Pagination fields are nested under meta.pagination (v4).
     *
     * @param  iterable<mixed>  $items
     * @param  string|null  $resourceClass
     * @throws UnexpectedValueException
     */
    public function respondWithOffsetPagination(
        iterable $items,
        int $offset,
        int $limit,
        int $total,
        ?string $resourceClass = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
    ): JsonResponse {
        $items = is_array($items) ? $items : iterator_to_array($items);
        $this->assertApiResourceClass($resourceClass);

        if ($resourceClass !== null) {
            /** @var class-string<JsonResource> $resourceClass */
            $items = $resourceClass::collection($items)->resolve();
        }

        $meta = [
            'pagination' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total,
                'has_more' => $offset + count($items) < $total,
            ],
        ];

        return $this->success($items, $message, $code, $meta);
    }

    /**
     * Convenience helper for returning a single item via an API Resource class.
     * When item_links config is true, pass $links or use item_links_default.
     *
     * @param  string  $resourceClass
     * @param  array<string, string>|null  $links  HAL-style links (e.g. self, index).
     *
     * @throws UnexpectedValueException
     */
    public function respondWithItem(mixed $item, string $resourceClass, ?array $links = null): JsonResponse
    {
        $this->assertApiResourceClass($resourceClass);

        /** @var class-string<JsonResource> $resourceClass */
        $resource = $resourceClass::make($item);
        $data = $resource->resolve();
        $meta = [];

        if (config('laravel-controller.item_links', true) === true) {
            $merged = array_merge(
                (array) config('laravel-controller.item_links_default', []),
                (array) $links,
            );
            if ($merged !== []) {
                $meta['links'] = $merged;
            }
        }

        return $this->success($data, 'Success', Response::HTTP_OK, $meta);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function respondWithResource(
        JsonResource $resource,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
    ): JsonResponse {
        return $this->success($resource, $message, $code);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function respondWithResourceCollection(
        ResourceCollection $collection,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
    ): JsonResponse {
        return $this->success($collection, $message, $code);
    }

    /**
     * Convenience helper for returning a collection via an API Resource class.
     *
     * @param  iterable<mixed>  $items
     * @param  string  $resourceClass
     *
     * @throws UnexpectedValueException
     */
    public function respondWithCollection(iterable $items, string $resourceClass): JsonResponse
    {
        $this->assertApiResourceClass($resourceClass);

        /** @var class-string<JsonResource> $resourceClass */
        $collection = $resourceClass::collection($items);

        return $this->success($collection);
    }
}
