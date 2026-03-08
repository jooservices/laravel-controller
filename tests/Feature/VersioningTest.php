<?php

namespace Tests\Feature;

use Tests\TestCase;

class VersioningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Since testbench might not auto-load routes from our custom path during setUp of provider easily
        // without configuring package path, we might need to rely on the Provider logic working
        // if base_path is correct.
    }

    public function testV1StatusRouteWorks()
    {
        $response = $this->getJson('/api/v1/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'status' => 'ok',
                    'message' => 'API is running',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'timestamp',
                ],
            ]);

        $this->assertNotNull($response->json('trace_id'), 'Trace ID should be present');
    }

    public function testStatusEndpointCanIncludeVersionEnvironmentAndMaintenance()
    {
        config([
            'laravel-controller.status.include_version' => true,
            'laravel-controller.status.include_environment' => true,
            'laravel-controller.status.include_maintenance' => true,
        ]);

        $response = $this->getJson('/api/v1/status');

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.message', 'API is running');
        $data = $response->json('data');
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('environment', $data);
        $this->assertArrayHasKey('maintenance', $data);
    }
}
