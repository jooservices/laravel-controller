<?php

namespace Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JOOservices\LaravelController\Traits\HasApiResponses;
use JsonSerializable;
use Tests\TestCase;

class HasApiResponsesTest extends TestCase
{
    protected $traitObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->traitObject = new class()
        {
            use HasApiResponses;
        };
    }

    public function testSuccessResponse()
    {
        $response = $this->traitObject->success(['foo' => 'bar'], 'Ok');
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Ok', $data['message']);
        $this->assertEquals(['foo' => 'bar'], $data['data']);
        $this->assertNotNull($data['trace_id']);
    }

    public function testCreatedResponse()
    {
        $response = $this->traitObject->created(['id' => 1]);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Created', $response->getData(true)['message']);
    }

    public function testNoContentResponse()
    {
        $response = $this->traitObject->noContent();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getData(true));
    }

    public function testBadRequestResponse()
    {
        $response = $this->traitObject->badRequest('Bad things happened');
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);
        $this->assertEquals('Bad things happened', $response->getData(true)['message']);
    }

    public function testUnauthorizedResponse()
    {
        $response = $this->traitObject->unauthorized();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testForbiddenResponse()
    {
        $response = $this->traitObject->forbidden();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testNotFoundResponse()
    {
        $response = $this->traitObject->notFound();
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUnprocessableResponse()
    {
        $errors = ['field' => ['Required']];
        $response = $this->traitObject->unprocessable(errors: $errors);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals($errors, $response->getData(true)['errors']);
    }

    public function testInternalErrorResponse()
    {
        $response = $this->traitObject->internalError();
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testResourceResponseHandling()
    {
        // Mock a simple resource
        $resource = new JsonResource(['id' => 1, 'name' => 'Test']);
        $response = $this->traitObject->success($resource);

        $data = $response->getData(true);
        $this->assertEquals(['id' => 1, 'name' => 'Test'], $data['data']);
    }

    public function testRespondWithDataAliasNormalizesArrayableAndJsonSerializable()
    {
        $serializable = new class() implements JsonSerializable
        {
            public function jsonSerialize(): mixed
            {
                return ['state' => 'ready'];
            }
        };

        $response = $this->traitObject->respondWithData([
            'collection' => new Collection(['one', 'two']),
            'serializable' => $serializable,
        ]);

        $data = $response->getData(true);

        $this->assertSame(['one', 'two'], $data['data']['collection']);
        $this->assertSame(['state' => 'ready'], $data['data']['serializable']);
    }

    public function testRespondWithErrorAlias()
    {
        $response = $this->traitObject->respondWithError('Invalid input', 400, ['field' => ['Invalid']]);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);
        $this->assertSame(['field' => ['Invalid']], $response->getData(true)['errors']);
    }

    public function testRespondNoContentAlias()
    {
        $response = $this->traitObject->respondNoContent();

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testRespondWithResourceAlias()
    {
        $resource = new JsonResource(['id' => 10]);

        $response = $this->traitObject->respondWithResource($resource, 'User retrieved');

        $this->assertSame('User retrieved', $response->getData(true)['message']);
        $this->assertSame(['id' => 10], $response->getData(true)['data']);
    }

    public function testResourceCollectionResponseHandling()
    {
        // Mock a resource collection
        $collection = JsonResource::collection(collect([['id' => 1], ['id' => 2]]));
        $response = $this->traitObject->success($collection);

        $data = $response->getData(true);
        $this->assertCount(2, $data['data']);
        $this->assertEquals(1, $data['data'][0]['id']);
    }

    public function testResourceCollectionKeepsAdditionalLinksUnderMetaLinks()
    {
        $collection = JsonResource::collection(collect([['id' => 1]]))
            ->additional([
                'meta' => ['page' => 1],
                'links' => ['self' => 'https://example.test/users'],
            ]);

        $response = $this->traitObject->success($collection);
        $data = $response->getData(true);

        $this->assertSame(['id' => 1], $data['data'][0]);
        $this->assertSame(1, $data['meta']['page']);
        $this->assertSame(['self' => 'https://example.test/users'], $data['meta']['links']);
    }

    public function testManualTraceId()
    {
        $uuid = (string) Str::uuid();
        request()->headers->set('X-Trace-ID', $uuid);

        $response = $this->traitObject->success();
        $this->assertEquals($uuid, $response->getData(true)['trace_id']);
    }

    public function testConfiguredTraceIdHeader()
    {
        config(['laravel-controller.trace_id.header' => 'X-Request-ID']);
        request()->headers->set('X-Request-ID', 'request-123');

        $response = $this->traitObject->success();

        $this->assertSame('request-123', $response->getData(true)['trace_id']);
    }

    public function testSuccessWithWarnings()
    {
        $warnings = ['deprecated' => 'This endpoint will be removed in v2.'];
        $response = $this->traitObject->success(['id' => 1], 'Ok', 200, [], $warnings);
        $data = $response->getData(true);
        $this->assertArrayHasKey('warnings', $data);
        $this->assertEquals($warnings, $data['warnings']);
    }

    public function testAcceptedResponse()
    {
        $response = $this->traitObject->accepted(['job_id' => 'abc'], 'Request accepted');
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertEquals('Request accepted', $response->getData(true)['message']);
        $this->assertEquals(['job_id' => 'abc'], $response->getData(true)['data']);
    }

    public function testConflictResponse()
    {
        $response = $this->traitObject->conflict('Resource already exists', ['field' => 'email']);
        $this->assertEquals(409, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);
        $this->assertEquals(['field' => 'email'], $response->getData(true)['errors']);
    }

    public function testGoneResponse()
    {
        $response = $this->traitObject->gone('Resource has been permanently removed');
        $this->assertEquals(410, $response->getStatusCode());
        $this->assertEquals('Resource has been permanently removed', $response->getData(true)['message']);
    }

    public function testNotFoundReturnsCorrectStatusAndMessage()
    {
        $response = $this->traitObject->notFound('User not found');
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('User not found', $response->getData(true)['message']);
    }

    public function testCreatedReturnsCorrectStatusAndMessage()
    {
        $response = $this->traitObject->created(['id' => 99]);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Created', $response->getData(true)['message']);
    }

    public function testNoContentReturnsCorrectStatus()
    {
        $response = $this->traitObject->noContent();
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testRespondWithItemFallsBackToSuccessWhenClassMissing()
    {
        $response = $this->traitObject->respondWithItem(['id' => 1, 'name' => 'Test'], 'NonExistentResource');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['id' => 1, 'name' => 'Test'], $response->getData(true)['data']);
    }

    public function testRespondWithPaginationIncludesLinksWhenConfigEnabled()
    {
        config(['laravel-controller.pagination_links' => true]);
        $paginator = new LengthAwarePaginator(
            [['id' => 1], ['id' => 2]],
            10,
            2,
            1,
            ['path' => request()->url()]
        );
        $response = $this->traitObject->respondWithPagination($paginator);
        $data = $response->getData(true);
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('links', $data['meta']);
        $this->assertArrayHasKey('first', $data['meta']['links']);
        $this->assertArrayHasKey('last', $data['meta']['links']);
        $this->assertArrayHasKey('prev', $data['meta']['links']);
        $this->assertArrayHasKey('next', $data['meta']['links']);
    }

    public function testRespondWithPaginationAcceptsMessageAndCode()
    {
        $paginator = new LengthAwarePaginator([['id' => 1]], 1, 1, 1);

        $response = $this->traitObject->respondWithPagination(
            paginator: $paginator,
            message: 'Users retrieved successfully.',
            code: 202,
        );

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('Users retrieved successfully.', $response->getData(true)['message']);
    }

    public function testSuccessResponseDoesNotIncludeWarningsWhenEmpty()
    {
        $response = $this->traitObject->success(['foo' => 'bar']);
        $data = $response->getData(true);

        $this->assertArrayNotHasKey('warnings', $data);
    }

    public function testNoContentCanReturnEnvelopeWhenConfigured()
    {
        config(['laravel-controller.envelope_204' => true]);

        $response = $this->traitObject->noContent();
        $data = $response->getData(true);

        $this->assertSame(204, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertSame(204, $data['code']);
        $this->assertNull($data['data']);
    }
}
