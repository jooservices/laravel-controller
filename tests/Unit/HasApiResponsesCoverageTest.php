<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Unit;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use JOOservices\LaravelController\Formatters\ProblemDetailsFormatter;
use JOOservices\LaravelController\OpenApi\EnvelopeContract;
use JOOservices\LaravelController\Tests\Support\ApiResponsesDouble;
use JOOservices\LaravelController\Tests\Support\FakeItemResource;
use JOOservices\LaravelController\Tests\TestCase;
use stdClass;
use UnexpectedValueException;

final class HasApiResponsesCoverageTest extends TestCase
{
    private ApiResponsesDouble $responses;

    protected function setUp(): void
    {
        parent::setUp();
        $this->responses = new ApiResponsesDouble();
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testTranslationsAreUsedWhenEnabled(): void
    {
        config(['laravel-controller.use_translations' => true]);

        $response = $this->responses->notFound();
        $data = $this->jsonPayload($response);

        self::assertSame(404, $response->getStatusCode());
        self::assertIsString($data['message']);
        self::assertNotSame('', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testInvalidFormatterThrowsUnexpectedValueException(): void
    {
        config(['laravel-controller.response_formatter' => stdClass::class]);

        $this->expectException(UnexpectedValueException::class);
        $this->responses->success(['ok' => true]);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testNormalizeObjectWithToArrayMethod(): void
    {
        $payload = new class {
            /**
             * @return array{label: string}
             */
            public function toArray(): array
            {
                return ['label' => fake()->word()];
            }
        };

        $response = $this->responses->success($payload);
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['data']);
        self::assertArrayHasKey('label', $data['data']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondTooManyRequestsFromRequestUsesHeader(): void
    {
        request()->headers->set('Retry-After', '42');

        $response = $this->responses->respondTooManyRequestsFromRequest();
        $data = $this->jsonPayload($response);

        self::assertSame(429, $response->getStatusCode());
        self::assertSame('42', $response->headers->get('Retry-After'));
        self::assertIsArray($data['errors']);
        /** @var array<string, mixed> $errors */
        $errors = $data['errors'];
        self::assertSame(42, $errors['retry_after']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondTooManyRequestsFromRequestFallsBackWhenHeaderMissing(): void
    {
        $response = $this->responses->respondTooManyRequestsFromRequest(defaultRetryAfter: 90);

        self::assertSame(429, $response->getStatusCode());
        self::assertSame('90', $response->headers->get('Retry-After'));
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithPaginationFallsBackForNonPaginator(): void
    {
        $items = [fake()->word(), fake()->word()];
        $response = $this->responses->respondWithPagination($items);
        $data = $this->jsonPayload($response);

        self::assertSame($items, $data['data']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithPaginationAppliesResourceClass(): void
    {
        $name = fake()->name();
        $paginator = new LengthAwarePaginator(
            [['id' => 1, 'name' => $name]],
            1,
            1,
            1,
        );

        $response = $this->responses->respondWithPagination($paginator, FakeItemResource::class);
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['data']);
        /** @var list<array<string, mixed>> $rows */
        $rows = $data['data'];
        self::assertSame($name, $rows[0]['name']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithCursorPagination(): void
    {
        $cursor = fake()->uuid();
        $next = fake()->uuid();
        $items = new Collection([['id' => 1], ['id' => 2]]);

        $response = $this->responses->respondWithCursorPagination($items, $cursor, $next);
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertIsArray($meta['pagination']);
        /** @var array<string, mixed> $pagination */
        $pagination = $meta['pagination'];
        self::assertSame($cursor, $pagination['cursor']);
        self::assertSame($next, $pagination['next_cursor']);
        self::assertTrue($pagination['has_more']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithCursorPaginationAppliesResourceClass(): void
    {
        $name = fake()->userName();
        $response = $this->responses->respondWithCursorPagination(
            [['id' => 3, 'name' => $name]],
            'a',
            null,
            FakeItemResource::class,
        );
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['data']);
        /** @var list<array<string, mixed>> $rows */
        $rows = $data['data'];
        self::assertSame($name, $rows[0]['name']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithOffsetPagination(): void
    {
        $response = $this->responses->respondWithOffsetPagination(
            [['id' => 1], ['id' => 2]],
            0,
            2,
            5,
        );
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertIsArray($meta['pagination']);
        /** @var array<string, mixed> $pagination */
        $pagination = $meta['pagination'];
        self::assertSame(0, $pagination['offset']);
        self::assertSame(2, $pagination['limit']);
        self::assertSame(5, $pagination['total']);
        self::assertTrue($pagination['has_more']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithItemIncludesLinksWhenConfigured(): void
    {
        $selfLink = fake()->url();
        config([
            'laravel-controller.item_links' => true,
            'laravel-controller.item_links_default' => ['index' => '/api/items'],
        ]);

        $response = $this->responses->respondWithItem(
            ['id' => 9, 'name' => fake()->word()],
            FakeItemResource::class,
            ['self' => $selfLink],
        );
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertIsArray($meta['links']);
        /** @var array<string, string> $links */
        $links = $meta['links'];
        self::assertSame('/api/items', $links['index']);
        self::assertSame($selfLink, $links['self']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithResourceCollectionAlias(): void
    {
        $collection = JsonResource::collection(collect([['id' => 1], ['id' => 2]]));
        $response = $this->responses->respondWithResourceCollection($collection, 'Listed');
        $data = $this->jsonPayload($response);

        self::assertSame('Listed', $data['message']);
        self::assertIsArray($data['data']);
        self::assertCount(2, $data['data']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithCollectionUsesResourceClass(): void
    {
        $name = fake()->firstName();
        $response = $this->responses->respondWithCollection(
            [['id' => 1, 'name' => $name]],
            FakeItemResource::class,
        );
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['data']);
        /** @var list<array<string, mixed>> $rows */
        $rows = $data['data'];
        self::assertSame($name, $rows[0]['name']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithCollectionRejectsMissingResourceClass(): void
    {
        $items = [['id' => fake()->randomNumber()]];
        $missingClass = 'Missing\\ResourceClassThatDoesNotExist';
        self::assertFalse(class_exists($missingClass));

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('API resource class [Missing\\ResourceClassThatDoesNotExist] must exist');

        $this->responses->respondWithCollection($items, $missingClass);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testUnprocessableAcceptsErrorsArrayAsFirstArgument(): void
    {
        $errors = ['email' => [fake()->sentence()]];
        $response = $this->responses->unprocessable($errors);
        $data = $this->jsonPayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame($errors, $data['errors']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testResponseKeyFallsBackWhenConfiguredKeyIsEmpty(): void
    {
        config(['laravel-controller.keys.success' => '']);

        $response = $this->responses->success(['x' => 1]);
        $data = $this->jsonPayload($response);

        self::assertArrayHasKey('success', $data);
        self::assertTrue($data['success']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithProblemReturnsRfc7807Payload(): void
    {
        $detail = fake()->sentence();
        $response = $this->responses->respondWithProblem(
            title: 'Validation failed',
            status: 422,
            detail: $detail,
            errors: ['email' => ['invalid']],
        );
        $data = $this->jsonPayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertStringContainsString(
            EnvelopeContract::CONTENT_TYPE_PROBLEM_JSON,
            (string) $response->headers->get('Content-Type'),
        );
        self::assertSame('https://jooservices.dev/problems/http-422', $data['type']);
        self::assertSame('Validation failed', $data['title']);
        self::assertSame(422, $data['status']);
        self::assertSame($detail, $data['detail']);
        self::assertSame(['email' => ['invalid']], $data['errors']);
        self::assertArrayHasKey('trace_id', $data);
        self::assertArrayNotHasKey('success', $data);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testProblemJsonProfileFormatsErrorsAsProblemDetails(): void
    {
        config([
            'laravel-controller.response_profile' => EnvelopeContract::PROFILE_PROBLEM_JSON,
            'laravel-controller.response_formatter' => null,
        ]);

        $response = $this->responses->error('Bad Request', 400);
        $data = $this->jsonPayload($response);

        self::assertStringContainsString(
            EnvelopeContract::CONTENT_TYPE_PROBLEM_JSON,
            (string) $response->headers->get('Content-Type'),
        );
        self::assertSame('Bad Request', $data['title']);
        self::assertSame(400, $data['status']);

        $ok = $this->responses->success(['id' => 1]);
        $okData = $this->jsonPayload($ok);
        self::assertTrue($okData['success']);
        self::assertStringContainsString('application/json', (string) $ok->headers->get('Content-Type'));
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testMetaHeadersAreEchoedWhenPresent(): void
    {
        $idempotencyKey = fake()->uuid();
        request()->headers->set('Idempotency-Key', $idempotencyKey);
        request()->headers->set('X-RateLimit-Limit', '100');
        request()->headers->set('X-RateLimit-Remaining', '99');
        request()->headers->set('X-RateLimit-Reset', '1700000000');
        request()->headers->set('Retry-After', '30');

        $response = $this->responses->success(['ok' => true]);
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertSame($idempotencyKey, $meta['idempotency_key']);
        self::assertSame([
            'limit' => '100',
            'remaining' => '99',
            'reset' => '1700000000',
        ], $meta['rate_limit']);
        self::assertSame('30', $meta['retry_after']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testMetaHeadersCanBeDisabled(): void
    {
        config(['laravel-controller.meta_headers.enabled' => false]);
        request()->headers->set('Idempotency-Key', fake()->uuid());

        $response = $this->responses->success(['ok' => true]);
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertArrayNotHasKey('idempotency_key', $meta);
    }

    public function testProblemDetailsFormatterContentTypeConstant(): void
    {
        self::assertSame(
            EnvelopeContract::CONTENT_TYPE_PROBLEM_JSON,
            ProblemDetailsFormatter::contentType(),
        );
    }
}
