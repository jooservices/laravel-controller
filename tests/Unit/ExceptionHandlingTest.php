<?php

namespace Tests\Unit;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use JOOservices\LaravelController\Traits\HandlesApiExceptions;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    public function testItHandlesModelNotFound()
    {
        $handler = new class()
        {
            use HandlesApiExceptions;
        };

        $exception = new ModelNotFoundException();
        $exception->setModel('User');

        $response = $handler->renderApiException($exception);

        $this->assertEquals(404, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals('No query results for model [User].', $data['message']); // Default fallback
    }

    public function testItHandlesValidationException()
    {
        $handler = new class()
        {
            use HandlesApiExceptions;
        };

        $validator = \Illuminate\Support\Facades\Validator::make([], ['field' => 'required']);
        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $response = $handler->renderApiException($e);

            $this->assertEquals(422, $response->getStatusCode());
            $data = $response->getData(true);
            $this->assertArrayHasKey('errors', $data);
        }
    }

    public function testItHandlesGenericException()
    {
        $handler = new class()
        {
            use HandlesApiExceptions;
        };

        $response = $handler->renderApiException(new Exception('Boom'));

        $this->assertEquals(500, $response->getStatusCode());
    }
}
