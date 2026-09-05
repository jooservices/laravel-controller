<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use UnexpectedValueException;

trait HandlesApiExceptions
{
    use HasApiResponses;

    /**
     * Render an exception into an API response.
     *
     * @throws UnexpectedValueException
     */
    public function renderApiException(Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            $message = $this->validationExceptionMessage($exception);

            return $this->unprocessable($message, $exception->errors());
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            $message = $exception->getMessage();

            return $this->notFound($message !== '' ? $message : 'Resource not found');
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

        $debug = config('app.debug');

        return $this->internalError(
            $debug === true ? $exception->getMessage() : 'Server Error',
        );
    }

    /**
     * Resolve the top-level message for ValidationException from config.
     * Config validation.message: string = fixed message, or "first" = first validation error message.
     */
    protected function validationExceptionMessage(ValidationException $exception): string
    {
        $configMessage = config('laravel-controller.validation.message', 'Unprocessable Entity');
        $configMessage = is_string($configMessage) ? $configMessage : 'Unprocessable Entity';

        if (strtolower($configMessage) === 'first') {
            $errors = $exception->errors();
            $first = reset($errors);

            if (! is_array($first)) {
                return is_string($first) ? $first : 'Unprocessable Entity';
            }

            $firstMessage = reset($first);

            return is_string($firstMessage) ? $firstMessage : 'Unprocessable Entity';
        }

        return $configMessage;
    }
}
