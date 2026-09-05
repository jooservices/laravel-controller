<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Unit;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use JOOservices\LaravelController\Tests\Support\ApiExceptionHandlerDouble;
use JOOservices\LaravelController\Tests\Support\UserModelStub;
use JOOservices\LaravelController\Tests\TestCase;
use OverflowException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnexpectedValueException;

final class ExceptionHandlingTest extends TestCase
{
    /**
     * @throws UnexpectedValueException
     */
    public function testItHandlesModelNotFound(): void
    {
        $handler = new ApiExceptionHandlerDouble();

        $exception = new ModelNotFoundException();
        $exception->setModel(UserModelStub::class);

        $response = $handler->renderApiException($exception);
        $data = $this->jsonPayload($response);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Resource not found', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItHandlesNotFoundHttpExceptionWithEmptyMessage(): void
    {
        $handler = new ApiExceptionHandlerDouble();
        $response = $handler->renderApiException(new NotFoundHttpException(''));
        $data = $this->jsonPayload($response);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Resource not found', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItHandlesModelNotFoundWrappedInNotFoundHttpException(): void
    {
        config(['app.debug' => false]);
        $handler = new ApiExceptionHandlerDouble();

        $modelNotFound = new ModelNotFoundException();
        $modelNotFound->setModel(UserModelStub::class, [42]);
        $exception = new NotFoundHttpException($modelNotFound->getMessage(), $modelNotFound);

        $response = $handler->renderApiException($exception);
        $data = $this->jsonPayload($response);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Resource not found', $data['message']);
        self::assertStringNotContainsString(UserModelStub::class, $data['message']);
        self::assertStringNotContainsString('42', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItHandlesValidationException(): void
    {
        $handler = new ApiExceptionHandlerDouble();
        $exception = ValidationException::withMessages([
            'field' => ['Required'],
        ]);

        $response = $handler->renderApiException($exception);
        $data = $this->jsonPayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertArrayHasKey('errors', $data);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testValidationMessageUsesFirstErrorWhenConfigured(): void
    {
        config(['laravel-controller.validation.message' => 'first']);
        $handler = new ApiExceptionHandlerDouble();
        $firstMessage = fake()->sentence();
        $exception = ValidationException::withMessages([
            'email' => [$firstMessage],
        ]);

        $response = $handler->renderApiException($exception);
        $data = $this->jsonPayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame($firstMessage, $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testValidationMessageFallsBackWhenConfigIsNotString(): void
    {
        config(['laravel-controller.validation.message' => ['bad']]);
        $handler = new ApiExceptionHandlerDouble();
        $exception = ValidationException::withMessages([
            'field' => [fake()->word()],
        ]);

        $response = $handler->renderApiException($exception);
        $data = $this->jsonPayload($response);

        self::assertSame('Unprocessable Entity', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItHandlesAuthenticationException(): void
    {
        $handler = new ApiExceptionHandlerDouble();
        $message = fake()->sentence();
        $response = $handler->renderApiException(new AuthenticationException($message));
        $data = $this->jsonPayload($response);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame($message, $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItHandlesAuthorizationException(): void
    {
        $handler = new ApiExceptionHandlerDouble();
        $message = fake()->sentence();
        $response = $handler->renderApiException(new AuthorizationException($message));
        $data = $this->jsonPayload($response);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame($message, $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItPreservesAuthorizationExceptionAsNotFoundStatus(): void
    {
        $handler = new ApiExceptionHandlerDouble();
        $message = fake()->sentence();
        $exception = (new AuthorizationException($message))->asNotFound();

        $response = $handler->renderApiException($exception);
        $data = $this->jsonPayload($response);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame($message, $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItHandlesHttpException(): void
    {
        $handler = new ApiExceptionHandlerDouble();
        $message = fake()->sentence();
        $response = $handler->renderApiException(new HttpException(418, $message));
        $data = $this->jsonPayload($response);

        self::assertSame(418, $response->getStatusCode());
        self::assertSame($message, $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItPreservesHttpExceptionHeaders(): void
    {
        $handler = new ApiExceptionHandlerDouble();
        $message = fake()->sentence();
        $retryAfter = (string) fake()->numberBetween(1, 120);
        $exception = new HttpException(429, $message, null, ['Retry-After' => $retryAfter]);

        $response = $handler->renderApiException($exception);

        self::assertSame(429, $response->getStatusCode());
        self::assertSame($retryAfter, $response->headers->get('Retry-After'));
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItHandlesGenericException(): void
    {
        $handler = new ApiExceptionHandlerDouble();
        $response = $handler->renderApiException(new Exception('Boom'));

        self::assertSame(500, $response->getStatusCode());
    }

    /**
     * @throws UnexpectedValueException
     * @throws OverflowException
     */
    public function testGenericExceptionExposesMessageWhenDebugEnabled(): void
    {
        config(['app.debug' => true]);
        $handler = new ApiExceptionHandlerDouble();
        $message = fake()->unique()->sentence();
        $response = $handler->renderApiException(new Exception($message));
        $data = $this->jsonPayload($response);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame($message, $data['message']);
    }
}
