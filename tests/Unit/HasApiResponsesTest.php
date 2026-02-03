<?php

namespace Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use JOOservices\LaravelController\Traits\HasApiResponses;
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
        $response = $this->traitObject->unprocessable($errors);
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

    public function testResourceCollectionResponseHandling()
    {
        // Mock a resource collection
        $collection = JsonResource::collection(collect([['id' => 1], ['id' => 2]]));
        $response = $this->traitObject->success($collection);

        $data = $response->getData(true);
        $this->assertCount(2, $data['data']);
        $this->assertEquals(1, $data['data'][0]['id']);
    }

    public function testManualTraceId()
    {
        $uuid = (string) Str::uuid();
        request()->headers->set('X-Trace-ID', $uuid);

        $response = $this->traitObject->success();
        $this->assertEquals($uuid, $response->getData(true)['trace_id']);
    }
}
