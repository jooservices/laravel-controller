<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
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
        return match (true) {
            $exception instanceof ValidationException => $this->renderValidationException($exception),
            $exception instanceof ModelNotFoundException => $this->renderModelNotFoundException($exception),
            $exception instanceof NotFoundHttpException => $this->renderNotFoundHttpException($exception),
            $exception instanceof AuthenticationException => $this->unauthorized($exception->getMessage()),
            $exception instanceof AuthorizationException => $this->renderAuthorizationException($exception),
            $exception instanceof HttpException => $this->renderHttpException($exception),
            default => $this->renderUnhandledException($exception),
        };
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function renderValidationException(ValidationException $exception): JsonResponse
    {
        return $this->unprocessable(
            $this->validationExceptionMessage($exception),
            $exception->errors(),
        );
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function renderModelNotFoundException(Throwable $exception): JsonResponse
    {
        report($exception);

        return $this->notFound('Resource not found');
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function renderNotFoundHttpException(NotFoundHttpException $exception): JsonResponse
    {
        $previous = $exception->getPrevious();
        if ($previous instanceof ModelNotFoundException) {
            return $this->renderModelNotFoundException($previous);
        }

        $message = $exception->getMessage();
        if ($this->isModelNotFoundMessage($message)) {
            report($exception);

            return $this->notFound('Resource not found');
        }

        return $this->notFound($message !== '' ? $message : 'Resource not found');
    }

    protected function isModelNotFoundMessage(string $message): bool
    {
        return str_contains($message, 'No query results for model');
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function renderAuthorizationException(AuthorizationException $exception): JsonResponse
    {
        $status = $exception->status() ?? Response::HTTP_FORBIDDEN;
        $message = $exception->getMessage();

        if ($message === '') {
            $message = $status === Response::HTTP_NOT_FOUND
                ? self::MESSAGE_NOT_FOUND
                : 'Forbidden';
        }

        return $this->error($message, $status);
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function renderHttpException(HttpException $exception): JsonResponse
    {
        return $this->error($exception->getMessage(), $exception->getStatusCode())
            ->withHeaders($exception->getHeaders());
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function renderUnhandledException(Throwable $exception): JsonResponse
    {
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
