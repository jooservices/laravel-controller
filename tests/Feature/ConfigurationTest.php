<?php

namespace Tests\Feature;

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
}
