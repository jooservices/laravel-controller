<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Unit;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
    public function testPaginatedAliasDelegatesToRespondWithPagination(): void
    {
        $paginator = new LengthAwarePaginator([['id' => 7]], 1, 1, 1);
        $response = $this->responses->paginated($paginator);
        $data = $this->jsonPayload($response);

        self::assertSame(200, $response->getStatusCode());
        self::assertIsArray($data['meta']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithCursorPagination(): void
    {
        $cursor = fake()->uuid();
        $next = fake()->uuid();
        $items = new Collection([['id' => 1], ['id' => 2]]);

        $response = $this->responses->respondWithCursorPagination($items, $cursor, $next, true);
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertSame($cursor, $meta['cursor']);
        self::assertSame($next, $meta['next_cursor']);
        self::assertTrue($meta['has_more']);
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
            false,
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
        self::assertSame(0, $meta['offset']);
        self::assertSame(2, $meta['limit']);
        self::assertSame(5, $meta['total']);
        self::assertTrue($meta['has_more']);
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
    public function testRespondWithCollectionFallsBackWhenClassMissing(): void
    {
        $items = [['id' => fake()->randomNumber()]];
        $missingClass = 'Missing\\ResourceClassThatDoesNotExist';
        self::assertFalse(class_exists($missingClass));

        $response = $this->responses->respondWithCollection($items, $missingClass);
        $data = $this->jsonPayload($response);

        self::assertSame($items, $data['data']);
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
}
