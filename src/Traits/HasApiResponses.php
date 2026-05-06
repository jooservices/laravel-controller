<?php

namespace JOOservices\LaravelController\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use JOOservices\LaravelController\Contracts\ResponseFormatter;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV4;
use UnexpectedValueException;

trait HasApiResponses
{
    private const MESSAGE_NOT_FOUND = 'Not Found';

    private const MESSAGE_UNPROCESSABLE = 'Unprocessable Entity';

    private const MESSAGE_TOO_MANY_REQUESTS = 'Too Many Requests';

    /**
     * Return a success response.
     *
     * @param  array<string, mixed>  $meta
     * @param  array<int, string>|array<string, string>  $warnings  Non-fatal warnings.
     */
    public function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
        array $meta = [],
        array $warnings = []
    ): JsonResponse {
        return $this->formatResponse(true, $code, $message, $data, null, $meta, $warnings);
    }

    /**
     * Return an error response.
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
     */
    public function respondWithData(
        mixed $data = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
        array $meta = [],
        array $warnings = []
    ): JsonResponse {
        return $this->success($data, $message, $code, $meta, $warnings);
    }

    /**
     * Laravel-friendly alias for returning an API error envelope.
     */
    public function respondWithError(
        string $message,
        int $code = Response::HTTP_BAD_REQUEST,
        mixed $errors = null
    ): JsonResponse {
        return $this->error($message, $code, $errors);
    }

    /**
     * Laravel-friendly alias for a 204 response.
     */
    public function respondNoContent(): JsonResponse
    {
        return $this->noContent();
    }

    /**
     * Return a created response (201).
     */
    public function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Return a no content response (204).
     * When config envelope_204 is true, returns the same envelope (data: null, trace_id, etc.).
     */
    public function noContent(): JsonResponse
    {
        if (config('laravel-controller.envelope_204', false) === true) {
            return $this->formatResponse(
                true,
                Response::HTTP_NO_CONTENT,
                $this->trans('No Content', 'no_content'),
                null,
                null,
                [],
                []
            );
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Return an accepted response (202). Use for async operations (e.g. "request accepted, processing").
     */
    public function accepted(mixed $data = null, string $message = 'Accepted'): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_ACCEPTED);
    }

    /**
     * Return a conflict response (409). Use for duplicate resource or version conflicts.
     */
    public function conflict(string $message = 'Conflict', mixed $errors = null): JsonResponse
    {
        return $this->error($message, Response::HTTP_CONFLICT, $errors);
    }

    /**
     * Return a gone response (410). Use for deprecated or permanently removed resources.
     */
    public function gone(string $message = 'Gone'): JsonResponse
    {
        return $this->error($message, Response::HTTP_GONE);
    }

    /**
     * Return a bad request response (400).
     */
    public function badRequest(string $message = 'Bad Request', mixed $errors = null): JsonResponse
    {
        return $this->error($message, Response::HTTP_BAD_REQUEST, $errors);
    }

    /**
     * Return an unauthorized response (401).
     */
    public function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        $msg = $message === 'Unauthorized' ? $this->trans('Unauthorized', 'unauthorized') : $message;

        return $this->error($msg, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Return a forbidden response (403).
     */
    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        $msg = $message === 'Forbidden' ? $this->trans('Forbidden', 'forbidden') : $message;

        return $this->error($msg, Response::HTTP_FORBIDDEN);
    }

    /**
     * Return a not found response (404).
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
     */
    public function unprocessable(
        string|array $messageOrErrors = self::MESSAGE_UNPROCESSABLE,
        mixed $errors = null
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
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    protected function formatResponse(
        bool $success,
        int $code,
        string $message,
        mixed $data = null,
        mixed $errors = null,
        array $meta = [],
        array $warnings = []
    ): JsonResponse {
        [$data, $meta] = $this->resolveResourcePayload($data, $meta);
        $data = $this->normalizeResponseValue($data);
        $errors = $this->normalizeResponseValue($errors);
        $meta = $this->normalizeStringKeyedArray($meta);

        $payload = $this->resolveResponsePayload([
            'success' => $this->isEffectiveSuccess($success, $code),
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
            'meta' => $meta,
            'warnings' => $this->normalizeWarnings($warnings),
            'trace_id' => $this->resolveTraceId(),
            'keys' => $this->configuredResponseKeys(),
        ]);

        return response()->json($payload, $code);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array{0: mixed, 1: array<string, mixed>}
     */
    protected function resolveResourcePayload(mixed $data, array $meta): array
    {
        if ($data instanceof ResourceCollection) {
            /** @var array<string, mixed> $response */
            $response = $data->response()->getData(true);
            $data = $response['data'] ?? [];
            $meta = array_merge($meta, (array) ($response['meta'] ?? []), (array) ($response['links'] ?? []));
        } elseif ($data instanceof JsonResource) {
            $data = $data->resolve();
        }

        return [$data, $this->normalizeStringKeyedArray($meta)];
    }

    protected function isEffectiveSuccess(bool $success, int $code): bool
    {
        $successCodes = config('laravel-controller.success_codes');

        if (! $success) {
            return false;
        }

        return is_array($successCodes) ? in_array($code, $successCodes, true) : true;
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
            static fn (mixed $value, mixed $key): bool => is_string($key) && is_string($value),
            ARRAY_FILTER_USE_BOTH
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
     */
    protected function resolveResponsePayload(array $response): array
    {
        $formatterClass = config('laravel-controller.response_formatter');

        if (is_string($formatterClass) && $formatterClass !== '') {
            $formatter = app($formatterClass);

            if (! $formatter instanceof ResponseFormatter) {
                throw new UnexpectedValueException(sprintf(
                    'Configured response formatter [%s] must implement %s.',
                    $formatterClass,
                    ResponseFormatter::class
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
            return array_map(fn (mixed $item): mixed => $this->normalizeResponseValue($item), $value);
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
     */
    public function tooManyRequests(
        string $message = self::MESSAGE_TOO_MANY_REQUESTS,
        int $retryAfter = 60
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
     */
    public function respondTooManyRequestsFromRequest(
        string $message = self::MESSAGE_TOO_MANY_REQUESTS,
        int $defaultRetryAfter = 60
    ): JsonResponse {
        $retryAfter = (int) request()->header('Retry-After', (string) $defaultRetryAfter);

        return $this->tooManyRequests($message, $retryAfter > 0 ? $retryAfter : $defaultRetryAfter);
    }

    /**
     * Return a paginated response. Use with LengthAwarePaginator; optionally pass a resource class to transform items.
     *
     * @param  class-string<JsonResource>|null  $resourceClass
     */
    public function respondWithPagination(
        mixed $paginator,
        ?string $resourceClass = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK
    ): JsonResponse {
        if ($paginator instanceof LengthAwarePaginator) {
            $items = $paginator->items();

            if ($resourceClass !== null && class_exists($resourceClass)) {
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
     * Return a cursor-paginated response. Meta has cursor, next_cursor, has_more.
     *
     * @param  iterable<mixed>  $items
     * @param  string|int|null  $cursor  Current cursor (opaque token or id).
     * @param  string|int|null  $nextCursor  Cursor for next page, or null if no next page.
     * @param  class-string<JsonResource>|null  $resourceClass
     */
    public function respondWithCursorPagination(
        iterable $items,
        $cursor,
        $nextCursor,
        bool $hasMore,
        ?string $resourceClass = null
    ): JsonResponse {
        $items = is_array($items) ? $items : iterator_to_array($items);
        if ($resourceClass !== null && class_exists($resourceClass)) {
            $items = $resourceClass::collection($items)->resolve();
        }

        $meta = [
            'cursor' => $cursor,
            'next_cursor' => $nextCursor,
            'has_more' => $hasMore,
        ];

        return $this->success($items, 'Success', Response::HTTP_OK, $meta);
    }

    /**
     * Return an offset-paginated response (offset/limit style). Meta has offset, limit, total, has_more.
     *
     * @param  iterable<mixed>  $items
     * @param  class-string<JsonResource>|null  $resourceClass
     */
    public function respondWithOffsetPagination(
        iterable $items,
        int $offset,
        int $limit,
        int $total,
        ?string $resourceClass = null
    ): JsonResponse {
        $items = is_array($items) ? $items : iterator_to_array($items);
        if ($resourceClass !== null && class_exists($resourceClass)) {
            $items = $resourceClass::collection($items)->resolve();
        }

        $meta = [
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
            'has_more' => $offset + count($items) < $total,
        ];

        return $this->success($items, 'Success', Response::HTTP_OK, $meta);
    }

    /**
     * @deprecated Use respondWithPagination() instead. Will be removed in the next major version.
     *
     * @param  class-string<JsonResource>|null  $resourceClass
     */
    public function paginated(mixed $paginator, ?string $resourceClass = null): JsonResponse
    {
        return $this->respondWithPagination($paginator, $resourceClass);
    }

    /**
     * Convenience helper for returning a single item via an API Resource class.
     * When item_links config is true, pass $links or use item_links_default.
     *
     * @param  class-string<JsonResource>  $resourceClass
     * @param  array<string, string>|null  $links  HAL-style links (e.g. self, index).
     */
    public function respondWithItem(mixed $item, string $resourceClass, ?array $links = null): JsonResponse
    {
        if (! class_exists($resourceClass)) {
            return $this->success($item);
        }

        $resource = $resourceClass::make($item);
        $data = $resource->resolve();
        $meta = [];

        if (config('laravel-controller.item_links', true) === true) {
            $merged = array_merge(
                (array) config('laravel-controller.item_links_default', []),
                (array) $links
            );
            if ($merged !== []) {
                $meta['links'] = $merged;
            }
        }

        return $this->success($data, 'Success', Response::HTTP_OK, $meta);
    }

    public function respondWithResource(
        JsonResource $resource,
        string $message = 'Success',
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return $this->success($resource, $message, $code);
    }

    public function respondWithResourceCollection(
        ResourceCollection $collection,
        string $message = 'Success',
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return $this->success($collection, $message, $code);
    }

    /**
     * Convenience helper for returning a collection via an API Resource class.
     *
     * @param  iterable<mixed>  $items
     * @param  class-string<JsonResource>  $resourceClass
     */
    public function respondWithCollection(iterable $items, string $resourceClass): JsonResponse
    {
        if (! class_exists($resourceClass)) {
            return $this->success($items);
        }

        $collection = $resourceClass::collection($items);

        return $this->success($collection);
    }
}
