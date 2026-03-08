<?php

namespace JOOservices\LaravelController\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait HandlesApiExceptions
{
    use HasApiResponses;

    /**
     * Render an exception into an API response.
     */
    public function renderApiException(Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            $message = $this->validationExceptionMessage($exception);

            return $this->unprocessable($message, $exception->errors());
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return $this->notFound($exception->getMessage() ?: 'Resource not found');
        }

        if ($exception instanceof AuthenticationException) {
            return $this->unauthorized($exception->getMessage());
        }

        if ($exception instanceof AuthorizationException) {
            return $this->forbidden($exception->getMessage());
        }

        if ($exception instanceof HttpException) {
            return $this->error($exception->getMessage(), $exception->getStatusCode());
        }

        return $this->internalError(
            config('app.debug') ? $exception->getMessage() : 'Server Error'
        );
    }

    /**
     * Resolve the top-level message for ValidationException from config.
     * Config validation.message: string = fixed message, or "first" = first validation error message.
     */
    protected function validationExceptionMessage(ValidationException $exception): string
    {
        $configMessage = config('laravel-controller.validation.message', 'Unprocessable Entity');

        if (strtolower($configMessage) === 'first') {
            $errors = $exception->errors();
            $first = reset($errors);

            return is_array($first) ? (string) reset($first) : (string) $first;
        }

        return $configMessage;
    }
}
