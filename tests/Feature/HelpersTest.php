<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use JOOservices\LaravelController\Traits\HasApiResponses;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function testTooManyRequestsReturns429()
    {
        $controller = new class()
        {
            use HasApiResponses;
        };

        $response = $controller->tooManyRequests('Slow down', 120);

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals('120', $response->headers->get('Retry-After'));

        $data = $response->getData(true);
        $this->assertEquals('Slow down', $data['message']);
    }

    public function testPaginatedFormatsCorrectly()
    {
        $controller = new class()
        {
            use HasApiResponses;
        };

        $items = collect(['a', 'b', 'c']);
        $paginator = new LengthAwarePaginator($items, 10, 5, 1);

        $response = $controller->paginated($paginator);
        $data = $response->getData(true);

        $this->assertEquals(['a', 'b', 'c'], $data['data']);
        $this->assertArrayHasKey('meta', $data);
        $this->assertEquals(1, $data['meta']['pagination']['current_page']);
        $this->assertEquals(10, $data['meta']['pagination']['total']);
    }
}
