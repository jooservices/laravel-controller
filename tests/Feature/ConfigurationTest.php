<?php

namespace Tests\Feature;

use App\Support\TestingResponseFormatter;
use JOOservices\LaravelController\Traits\HasApiResponses;
use Tests\TestCase;

class ConfigurationTest extends TestCase
{
    public function testItUsesConfiguredKeysForResponse()
    {
        // Mock a class using the trait
        $controller = new class()
        {
            use HasApiResponses;
        };

        // Override config
        config(['laravel-controller.keys.success' => 'status']);
        config(['laravel-controller.keys.message' => 'msg']);

        $response = $controller->success(['foo' => 'bar']);
        $data = $response->getData(true);

        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('msg', $data);
        $this->assertArrayNotHasKey('success', $data);
        $this->assertTrue($data['status']);
    }

    public function testItUsesCustomResponseFormatterWhenConfigured()
    {
        $controller = new class()
        {
            use HasApiResponses;
        };

        config(['laravel-controller.response_formatter' => TestingResponseFormatter::class]);

        $response = $controller->success(['foo' => 'bar'], 'Ok', 200, ['page' => 1], ['deprecated']);
        $data = $response->getData(true);

        $this->assertTrue($data['ok']);
        $this->assertSame(200, $data['status']);
        $this->assertSame('Ok', $data['message']);
        $this->assertSame(['foo' => 'bar'], $data['result']);
        $this->assertNull($data['issues']);
        $this->assertSame(['page' => 1], $data['diagnostics']['meta']);
        $this->assertSame(['deprecated'], $data['diagnostics']['warnings']);
        $this->assertNotEmpty($data['diagnostics']['request_id']);
        $this->assertArrayNotHasKey('success', $data);
    }

    public function testDefaultConfigurationIncludesHostRouteAutoMappingSwitch()
    {
        $this->assertTrue(config('laravel-controller.routes.auto_map_host_routes'));
    }
}
