<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Feature;

use JOOservices\LaravelController\Tests\TestCase;

final class VersioningTest extends TestCase
{
    public function testV1StatusRouteWorks(): void
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

        self::assertNotNull($response->json('trace_id'), 'Trace ID should be present');
    }

    public function testStatusEndpointCanIncludeVersionEnvironmentAndMaintenance(): void
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
        self::assertIsArray($data);
        self::assertArrayHasKey('version', $data);
        self::assertArrayHasKey('environment', $data);
        self::assertArrayHasKey('maintenance', $data);
    }
}
