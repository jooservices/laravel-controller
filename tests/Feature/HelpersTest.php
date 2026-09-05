<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use JOOservices\LaravelController\Tests\Support\ApiResponsesDouble;
use JOOservices\LaravelController\Tests\TestCase;
use UnexpectedValueException;

final class HelpersTest extends TestCase
{
    /**
     * @throws UnexpectedValueException
     */
    public function testTooManyRequestsReturns429(): void
    {
        $controller = new ApiResponsesDouble();

        $response = $controller->tooManyRequests('Slow down', 120);

        self::assertSame(429, $response->getStatusCode());
        self::assertSame('120', $response->headers->get('Retry-After'));

        $data = $this->jsonPayload($response);
        self::assertSame('Slow down', $data['message']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testRespondWithPaginationFormatsCorrectly(): void
    {
        $controller = new ApiResponsesDouble();

        $items = collect(['a', 'b', 'c']);
        $paginator = new LengthAwarePaginator($items, 10, 5, 1);

        $response = $controller->respondWithPagination($paginator);
        $data = $this->jsonPayload($response);

        self::assertSame(['a', 'b', 'c'], $data['data']);
        self::assertArrayHasKey('meta', $data);
        self::assertIsArray($data['meta']);
        /** @var array<string, mixed> $meta */
        $meta = $data['meta'];
        self::assertIsArray($meta['pagination']);
        /** @var array<string, mixed> $pagination */
        $pagination = $meta['pagination'];
        self::assertSame(1, $pagination['current_page']);
        self::assertSame(10, $pagination['total']);
    }
}
