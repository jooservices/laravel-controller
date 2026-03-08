<?php

namespace JOOservices\LaravelController\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

trait HasApiResponses
{
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
        if (config('laravel-controller.envelope_204', false)) {
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
    public function notFound(string $message = 'Not Found'): JsonResponse
    {
        $msg = $message === 'Not Found' ? $this->trans('Not Found', 'not_found') : $message;

        return $this->error($msg, Response::HTTP_NOT_FOUND);
    }

    /**
     * Return an unprocessable entity response (422). Validation errors go in $errors.
     * For backward compatibility, the first argument may be an array of errors (message then defaults).
     */
    public function unprocessable(string|array $messageOrErrors = 'Unprocessable Entity', mixed $errors = null): JsonResponse
    {
        if (is_array($messageOrErrors)) {
            $errors = $messageOrErrors;
            $messageOrErrors = 'Unprocessable Entity';
        }
        $msg = $messageOrErrors === 'Unprocessable Entity'
            ? $this->trans('Unprocessable Entity', 'unprocessable')
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
        if (! config('laravel-controller.use_translations', false)) {
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
     * @SuppressWarnings(PHPMD.StaticAccess)
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
        if ($data instanceof ResourceCollection) {
            /** @var array<string, mixed> $response */
            $response = $data->response()->getData(true);
            $data = $response['data'] ?? [];
            $meta = array_merge($meta, (array) ($response['meta'] ?? []), (array) ($response['links'] ?? []));
        } elseif ($data instanceof JsonResource) {
            $data = $data->resolve();
        }

        $successCodes = config('laravel-controller.success_codes');
        $effectiveSuccess = ! $success
            ? false
            : (is_array($successCodes) ? in_array($code, $successCodes, true) : $success);

        $payload = [
            config('laravel-controller.keys.success', 'success') => $effectiveSuccess,
            config('laravel-controller.keys.code', 'code') => $code,
            config('laravel-controller.keys.message', 'message') => $message,
            config('laravel-controller.keys.data', 'data') => $data,
            config('laravel-controller.keys.meta', 'meta') => (object) $meta,
            config('laravel-controller.keys.errors', 'errors') => $errors,
            config('laravel-controller.keys.trace_id', 'trace_id') => request()->header('X-Trace-ID')
                ?? (string) Str::uuid(),
        ];

        if ($warnings !== []) {
            $payload[config('laravel-controller.keys.warnings', 'warnings')] = $warnings;
        }

        return response()->json($payload, $code);
    }

    /**
     * Return a too many requests response (429).
     * Use respondTooManyRequestsFromRequest() to derive retry_after from Laravel's rate limiter.
     */
    public function tooManyRequests(string $message = 'Too Many Requests', int $retryAfter = 60): JsonResponse
    {
        $msg = $message === 'Too Many Requests' ? $this->trans('Too Many Requests', 'too_many_requests') : $message;

        return $this->error($msg, Response::HTTP_TOO_MANY_REQUESTS, [
            'retry_after' => $retryAfter,
        ])->withHeaders(['Retry-After' => (string) $retryAfter]);
    }

    /**
     * Return 429 using Retry-After from request (e.g. throttle or custom limiter).
     * Falls back to $defaultRetryAfter seconds when header is missing.
     */
    public function respondTooManyRequestsFromRequest(
        string $message = 'Too Many Requests',
        int $defaultRetryAfter = 60
    ): JsonResponse {
        $retryAfter = (int) request()->header('Retry-After', (string) $defaultRetryAfter);

        return $this->tooManyRequests($message, $retryAfter > 0 ? $retryAfter : $defaultRetryAfter);
    }

    /**
     * Return a paginated response. Use with LengthAwarePaginator; optionally pass a resource class to transform items.
     *
     * @param  class-string<\Illuminate\Http\Resources\Json\JsonResource>|null  $resourceClass
     */
    public function respondWithPagination(mixed $paginator, ?string $resourceClass = null): JsonResponse
    {
        if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $items = $paginator->items();

            if ($resourceClass && class_exists($resourceClass)) {
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

            if (config('laravel-controller.pagination_links', true)) {
                $meta['links'] = [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ];
            }

            return $this->success($items, 'Success', Response::HTTP_OK, $meta);
        }

        return $this->success($paginator);
    }

    /**
     * Return a cursor-paginated response. Meta has cursor, next_cursor, has_more.
     *
     * @param  iterable<mixed>  $items
     * @param  string|int|null  $cursor  Current cursor (opaque token or id).
     * @param  string|int|null  $nextCursor  Cursor for next page, or null if no next page.
     * @param  class-string<\Illuminate\Http\Resources\Json\JsonResource>|null  $resourceClass
     */
    public function respondWithCursorPagination(
        iterable $items,
        $cursor,
        $nextCursor,
        bool $hasMore,
        ?string $resourceClass = null
    ): JsonResponse {
        $items = is_array($items) ? $items : iterator_to_array($items);
        if ($resourceClass && class_exists($resourceClass)) {
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
     * @param  class-string<\Illuminate\Http\Resources\Json\JsonResource>|null  $resourceClass
     */
    public function respondWithOffsetPagination(
        iterable $items,
        int $offset,
        int $limit,
        int $total,
        ?string $resourceClass = null
    ): JsonResponse {
        $items = is_array($items) ? $items : iterator_to_array($items);
        if ($resourceClass && class_exists($resourceClass)) {
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
     * @param  class-string<\Illuminate\Http\Resources\Json\JsonResource>|null  $resourceClass
     */
    public function paginated(mixed $paginator, ?string $resourceClass = null): JsonResponse
    {
        return $this->respondWithPagination($paginator, $resourceClass);
    }

    /**
     * Convenience helper for returning a single item via an API Resource class.
     * When item_links config is true, pass $links or use item_links_default.
     *
     * @param  class-string<\Illuminate\Http\Resources\Json\JsonResource>  $resourceClass
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

        if (config('laravel-controller.item_links', true)) {
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

    /**
     * Convenience helper for returning a collection via an API Resource class.
     *
     * @param  iterable<mixed>  $items
     * @param  class-string<\Illuminate\Http\Resources\Json\JsonResource>  $resourceClass
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
