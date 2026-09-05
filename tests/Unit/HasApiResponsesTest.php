<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Unit;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JOOservices\LaravelController\Tests\Support\ApiResponsesDouble;
use JOOservices\LaravelController\Tests\Support\FakeItemResource;
use JOOservices\LaravelController\Tests\TestCase;
use JsonSerializable;
use UnexpectedValueException;

final class HasApiResponsesTest extends TestCase
{
    private ApiResponsesDouble $traitObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->traitObject = new ApiResponsesDouble();
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testSuccessResponse(): void
    {
        $response = $this->traitObject->success(['foo' => 'bar'], 'Ok');
        self::assertSame(200, $response->getStatusCode());

        $data = $this->jsonPayload($response);
        self::assertTrue($data['success']);
        self::assertSame('Ok', $data['message']);
        self::assertSame(['foo' => 'bar'], $data['data']);
        self::assertNotNull($data['trace_id']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testCreatedResponse(): void
    {
        $response = $this->traitObject->created(['id' => 1]);
        $data = $this->jsonPayload($response);
        self::assertSame(201, $response->getStatusCode());
        self::assertSame('Created', $data['message']);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testNoContentResponse(): void
    {
        $response = $this->traitObject->noContent();
        self::assertSame(204, $response->getStatusCode());
        self::assertSame('', $response->getContent());
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testBadRequestResponse(): void
    {
        $response = $this->traitObject->badRequest('Bad things happened');
        $data = $this->jsonPayload($response);
        self::assertSame(400, $response->getStatusCode());
        self::assertFalse($data['success']);
        self::assertSame('Bad things happened', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testUnauthorizedResponse(): void
    {
        $response = $this->traitObject->unauthorized();
        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testForbiddenResponse(): void
    {
        $response = $this->traitObject->forbidden();
        self::assertSame(403, $response->getStatusCode());
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testNotFoundResponse(): void
    {
        $response = $this->traitObject->notFound();
        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testUnprocessableResponse(): void
    {
        $errors = ['field' => ['Required']];
        $response = $this->traitObject->unprocessable(errors: $errors);
        $data = $this->jsonPayload($response);
        self::assertSame(422, $response->getStatusCode());
        self::assertSame($errors, $data['errors']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testInternalErrorResponse(): void
    {
        $response = $this->traitObject->internalError();
        self::assertSame(500, $response->getStatusCode());
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testResourceResponseHandling(): void
    {
        $resource = new JsonResource(['id' => 1, 'name' => 'Test']);
        $response = $this->traitObject->success($resource);
        $data = $this->jsonPayload($response);
        self::assertSame(['id' => 1, 'name' => 'Test'], $data['data']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithDataAliasNormalizesArrayableAndJsonSerializable(): void
    {
        $serializable = new class implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['state' => 'ready'];
            }
        };

        $response = $this->traitObject->respondWithData([
            'collection' => new Collection(['one', 'two']),
            'serializable' => $serializable,
        ]);

        $data = $this->jsonPayload($response);
        self::assertIsArray($data['data']);
        /** @var array<string, mixed> $payload */
        $payload = $data['data'];
        self::assertSame(['one', 'two'], $payload['collection']);
        self::assertSame(['state' => 'ready'], $payload['serializable']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithErrorAlias(): void
    {
        $response = $this->traitObject->respondWithError('Invalid input', 400, ['field' => ['Invalid']]);
        $data = $this->jsonPayload($response);

        self::assertSame(400, $response->getStatusCode());
        self::assertFalse($data['success']);
        self::assertSame(['field' => ['Invalid']], $data['errors']);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRespondNoContentAlias(): void
    {
        $response = $this->traitObject->respondNoContent();
        self::assertSame(204, $response->getStatusCode());
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithResourceAlias(): void
    {
        $resource = new JsonResource(['id' => 10]);
        $response = $this->traitObject->respondWithResource($resource, 'User retrieved');
        $data = $this->jsonPayload($response);

        self::assertSame('User retrieved', $data['message']);
        self::assertSame(['id' => 10], $data['data']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testResourceCollectionResponseHandling(): void
    {
        $collection = JsonResource::collection(collect([['id' => 1], ['id' => 2]]));
        $response = $this->traitObject->success($collection);
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['data']);
        /** @var list<array<string, mixed>> $items */
        $items = $data['data'];
        self::assertCount(2, $items);
        self::assertSame(1, $items[0]['id']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testResourceCollectionKeepsAdditionalLinksUnderMetaLinks(): void
    {
        $collection = JsonResource::collection(collect([['id' => 1]]))
            ->additional([
                'meta' => ['page' => 1],
                'links' => ['self' => 'https://example.test/users'],
            ]);

        $response = $this->traitObject->success($collection);
        $data = $this->jsonPayload($response);

        self::assertIsArray($data['data']);
        /** @var list<array<string, mixed>> $items */
        $items = $data['data'];
        self::assertSame(['id' => 1], $items[0]);

        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertSame(1, $meta['page']);
        self::assertSame(['self' => 'https://example.test/users'], $meta['links']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testSingleResourceKeepsAdditionalMetadataInMeta(): void
    {
        $etag = fake()->sha256();
        $resource = (new FakeItemResource(['id' => 9, 'name' => 'Ada', 'internal_note' => 'secret']))
            ->additional(['etag' => $etag]);

        $response = $this->traitObject->respondWithResource($resource);
        $data = $this->jsonPayload($response);

        self::assertSame(['id' => 9, 'name' => 'Ada'], $data['data']);
        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertSame($etag, $meta['etag']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testManualTraceId(): void
    {
        $uuid = (string) Str::uuid();
        request()->headers->set('X-Trace-ID', $uuid);

        $response = $this->traitObject->success();
        $data = $this->jsonPayload($response);
        self::assertSame($uuid, $data['trace_id']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testConfiguredTraceIdHeader(): void
    {
        config(['laravel-controller.trace_id.header' => 'X-Request-ID']);
        request()->headers->set('X-Request-ID', 'request-123');

        $response = $this->traitObject->success();
        $data = $this->jsonPayload($response);
        self::assertSame('request-123', $data['trace_id']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testSuccessWithWarnings(): void
    {
        $warnings = ['deprecated' => 'This endpoint will be removed in v2.'];
        $response = $this->traitObject->success(['id' => 1], 'Ok', 200, [], $warnings);
        $data = $this->jsonPayload($response);
        self::assertArrayHasKey('warnings', $data);
        self::assertSame($warnings, $data['warnings']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testAcceptedResponse(): void
    {
        $response = $this->traitObject->accepted(['job_id' => 'abc'], 'Request accepted');
        $data = $this->jsonPayload($response);
        self::assertSame(202, $response->getStatusCode());
        self::assertSame('Request accepted', $data['message']);
        self::assertSame(['job_id' => 'abc'], $data['data']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testConflictResponse(): void
    {
        $response = $this->traitObject->conflict('Resource already exists', ['field' => 'email']);
        $data = $this->jsonPayload($response);
        self::assertSame(409, $response->getStatusCode());
        self::assertFalse($data['success']);
        self::assertSame(['field' => 'email'], $data['errors']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testGoneResponse(): void
    {
        $response = $this->traitObject->gone('Resource has been permanently removed');
        $data = $this->jsonPayload($response);
        self::assertSame(410, $response->getStatusCode());
        self::assertSame('Resource has been permanently removed', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testNotFoundReturnsCorrectStatusAndMessage(): void
    {
        $response = $this->traitObject->notFound('User not found');
        $data = $this->jsonPayload($response);
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('User not found', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testCreatedReturnsCorrectStatusAndMessage(): void
    {
        $response = $this->traitObject->created(['id' => 99]);
        $data = $this->jsonPayload($response);
        self::assertSame(201, $response->getStatusCode());
        self::assertSame('Created', $data['message']);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testNoContentReturnsCorrectStatus(): void
    {
        $response = $this->traitObject->noContent();
        self::assertSame(204, $response->getStatusCode());
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithItemRejectsMissingResourceClass(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('API resource class [NonExistentResource] must exist');

        $this->traitObject->respondWithItem(
            ['id' => 1, 'name' => 'Test', 'internal_note' => 'secret'],
            'NonExistentResource',
        );
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithPaginationIncludesLinksWhenConfigEnabled(): void
    {
        config(['laravel-controller.pagination_links' => true]);
        $paginator = new LengthAwarePaginator(
            [['id' => 1], ['id' => 2]],
            10,
            2,
            1,
            ['path' => request()->url()],
        );
        $response = $this->traitObject->respondWithPagination($paginator);
        $data = $this->jsonPayload($response);
        self::assertArrayHasKey('meta', $data);
        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertArrayHasKey('links', $meta);
        self::assertIsArray($meta['links']);
        /** @var array<string, mixed> $links */
        $links = $meta['links'];
        self::assertArrayHasKey('first', $links);
        self::assertArrayHasKey('last', $links);
        self::assertArrayHasKey('prev', $links);
        self::assertArrayHasKey('next', $links);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithPaginationAcceptsMessageAndCode(): void
    {
        $paginator = new LengthAwarePaginator([['id' => 1]], 1, 1, 1);

        $response = $this->traitObject->respondWithPagination(
            paginator: $paginator,
            message: 'Users retrieved successfully.',
            code: 202,
        );

        $data = $this->jsonPayload($response);
        self::assertSame(202, $response->getStatusCode());
        self::assertSame('Users retrieved successfully.', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testSuccessResponseDoesNotIncludeWarningsWhenEmpty(): void
    {
        $response = $this->traitObject->success(['foo' => 'bar']);
        $data = $this->jsonPayload($response);
        self::assertArrayNotHasKey('warnings', $data);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testNoContentReturnsEmptyBodyWithoutEnvelope(): void
    {
        $response = $this->traitObject->noContent();

        self::assertSame(204, $response->getStatusCode());
        self::assertSame('', $response->getContent());
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testSuccessWithNon2xxCodeDefaultsSuccessFalse(): void
    {
        $response = $this->traitObject->success(['id' => 1], 'Oops', 500);
        $data = $this->jsonPayload($response);

        self::assertSame(500, $response->getStatusCode());
        self::assertFalse($data['success']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testResourceCollectionWithoutWrappingKeepsItems(): void
    {
        JsonResource::withoutWrapping();

        try {
            $name = fake()->firstName();
            $collection = FakeItemResource::collection([
                ['id' => 1, 'name' => $name, 'internal_note' => 'secret'],
            ]);
            $response = $this->traitObject->respondWithResourceCollection($collection);
            $data = $this->jsonPayload($response);

            self::assertIsArray($data['data']);
            self::assertCount(1, $data['data']);
            /** @var list<array<string, mixed>> $rows */
            $rows = $data['data'];
            self::assertSame($name, $rows[0]['name']);
            self::assertArrayNotHasKey('internal_note', $rows[0]);
        } finally {
            JsonResource::wrap('data');
        }
    }
}
