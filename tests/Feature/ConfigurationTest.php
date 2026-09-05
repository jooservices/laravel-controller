<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Feature;

use App\Support\TestingResponseFormatter;
use JOOservices\LaravelController\Tests\Support\ApiResponsesDouble;
use JOOservices\LaravelController\Tests\TestCase;
use UnexpectedValueException;

final class ConfigurationTest extends TestCase
{
    /**
     * @throws UnexpectedValueException
     */
    public function testItUsesConfiguredKeysForResponse(): void
    {
        $controller = new ApiResponsesDouble();

        config(['laravel-controller.keys.success' => 'status']);
        config(['laravel-controller.keys.message' => 'msg']);

        $response = $controller->success(['foo' => 'bar']);
        $data = $this->jsonPayload($response);

        self::assertArrayHasKey('status', $data);
        self::assertArrayHasKey('msg', $data);
        self::assertArrayNotHasKey('success', $data);
        self::assertTrue($data['status']);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testItUsesCustomResponseFormatterWhenConfigured(): void
    {
        $controller = new ApiResponsesDouble();

        config(['laravel-controller.response_formatter' => TestingResponseFormatter::class]);

        $response = $controller->success(['foo' => 'bar'], 'Ok', 200, ['page' => 1], ['deprecated']);
        $data = $this->jsonPayload($response);

        self::assertTrue($data['ok']);
        self::assertSame(200, $data['status']);
        self::assertSame('Ok', $data['message']);
        self::assertSame(['foo' => 'bar'], $data['result']);
        self::assertNull($data['issues']);
        self::assertIsArray($data['diagnostics']);
        /** @var array<string, mixed> $diagnostics */
        $diagnostics = $data['diagnostics'];
        self::assertSame(['page' => 1], $diagnostics['meta']);
        self::assertSame(['deprecated'], $diagnostics['warnings']);
        self::assertNotEmpty($diagnostics['request_id']);
        self::assertArrayNotHasKey('success', $data);
    }

    public function testDefaultConfigurationIncludesHostRouteAutoMappingSwitch(): void
    {
        self::assertTrue((bool) config('laravel-controller.routes.auto_map_host_routes'));
    }
}
