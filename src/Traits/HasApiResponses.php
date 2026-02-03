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
     */
    public function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
        array $meta = []
    ): JsonResponse {
        return $this->formatResponse(true, $code, $message, $data, null, $meta);
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
     */
    public function noContent(): JsonResponse
    {
        return response()->json([], Response::HTTP_NO_CONTENT);
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
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Return a forbidden response (403).
     */
    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Return a not found response (404).
     */
    public function notFound(string $message = 'Not Found'): JsonResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Return an unprocessable entity response (422).
     */
    public function unprocessable(mixed $errors, string $message = 'Unprocessable Entity'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Return an internal server error response (500).
     */
    public function internalError(string $message = 'Internal Server Error'): JsonResponse
    {
        return $this->error($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Format the response structure.
     *
     * @param  array<string, mixed>  $meta
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function formatResponse(
        bool $success,
        int $code,
        string $message,
        mixed $data = null,
        mixed $errors = null,
        array $meta = []
    ): JsonResponse {
        if ($data instanceof ResourceCollection) {
            /** @var array<string, mixed> $response */
            $response = $data->response()->getData(true);
            $data = $response['data'] ?? [];
            $meta = array_merge($meta, (array) ($response['meta'] ?? []), (array) ($response['links'] ?? []));
        } elseif ($data instanceof JsonResource) {
            $data = $data->resolve();
        }

        return response()->json([
            config('laravel-controller.keys.success', 'success') => $success,
            config('laravel-controller.keys.code', 'code') => $code,
            config('laravel-controller.keys.message', 'message') => $message,
            config('laravel-controller.keys.data', 'data') => $data,
            config('laravel-controller.keys.meta', 'meta') => (object) $meta,
            config('laravel-controller.keys.errors', 'errors') => $errors,
            config('laravel-controller.keys.trace_id', 'trace_id') => request()->header('X-Trace-ID')
                ?? (string) Str::uuid(),
        ], $code);
    }

    /**
     * Return a too many requests response (429).
     */
    public function tooManyRequests(string $message = 'Too Many Requests', int $retryAfter = 60): JsonResponse
    {
        return $this->error($message, Response::HTTP_TOO_MANY_REQUESTS, [
            'retry_after' => $retryAfter,
        ])->withHeaders(['Retry-After' => $retryAfter]);
    }

    /**
     * Return a paginated response.
     */
    public function paginated(mixed $paginator, ?string $resourceClass = null): JsonResponse
    {
        if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $items = $paginator->items();

            if ($resourceClass && class_exists($resourceClass)) {
                $items = $resourceClass::collection($items);
            }

            return $this->success(
                $items,
                'Success',
                Response::HTTP_OK,
                [
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'total' => $paginator->total(),
                        'per_page' => $paginator->perPage(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ]
            );
        }

        return $this->success($paginator);
    }
}
