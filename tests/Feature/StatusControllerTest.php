<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Feature;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use JOOservices\LaravelController\Tests\Support\FakeStatusHealthCheck;
use JOOservices\LaravelController\Tests\Support\SlowStatusHealthChecker;
use JOOservices\LaravelController\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use OverflowException;
use PDO;
use RuntimeException;

final class StatusControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testStatusIncludesHealthChecksWhenConfigured(): void
    {
        config([
            'laravel-controller.status.checks' => ['cache', 'unknown_probe'],
            'laravel-controller.status.checks_timeout_seconds' => '5',
            'app.version' => fake()->semver(),
            'laravel-controller.status.include_version' => true,
        ]);

        $cache = Mockery::mock(CacheRepository::class);
        $cache->shouldReceive('put')->once()->with('laravel_controller_health', 1, 10)->andReturn(true);
        $cache->shouldReceive('get')->once()->with('laravel_controller_health')->andReturn(1);
        $this->app()->instance(CacheRepository::class, $cache);

        $response = $this->getJson('/api/v1/status');

        $response->assertStatus(503);
        $data = $response->json('data');
        self::assertIsArray($data);
        self::assertSame('unavailable', $data['status']);
        self::assertArrayHasKey('checks', $data);
        self::assertIsArray($data['checks']);
        /** @var array<string, array{ok: bool, message?: string}> $checks */
        $checks = $data['checks'];
        self::assertTrue($checks['cache']['ok']);
        self::assertFalse($checks['unknown_probe']['ok']);
        self::assertArrayHasKey('message', $checks['unknown_probe']);
        self::assertSame('unknown check', $checks['unknown_probe']['message']);
        self::assertArrayHasKey('version', $data);
    }

    public function testStatusReportsDatabaseAndQueueSuccess(): void
    {
        config([
            'laravel-controller.status.checks' => ['database', 'queue'],
            'laravel-controller.status.checks_timeout_seconds' => 2,
        ]);

        $connection = Mockery::mock();
        $connection->shouldReceive('getPdo')->once()->andReturn(Mockery::mock(PDO::class));
        DB::shouldReceive('connection')->once()->andReturn($connection);

        $queueConnection = Mockery::mock();
        $queueConnection->shouldReceive('size')->once()->andReturn(0);
        Queue::shouldReceive('connection')->once()->andReturn($queueConnection);

        $response = $this->getJson('/api/v1/status');

        $response->assertOk();
        $checks = $response->json('data.checks');
        self::assertIsArray($checks);
        self::assertIsArray($checks['database']);
        self::assertIsArray($checks['queue']);
        self::assertTrue($checks['database']['ok']);
        self::assertTrue($checks['queue']['ok']);
    }

    public function testStatusHealthCheckFailureUsesSafeMessageWhenDebugOff(): void
    {
        config([
            'laravel-controller.status.checks' => ['database'],
            'app.debug' => false,
        ]);

        $connection = Mockery::mock();
        $connection->shouldReceive('getPdo')->once()->andThrow(new RuntimeException(fake()->sentence()));
        DB::shouldReceive('connection')->once()->andReturn($connection);

        $response = $this->getJson('/api/v1/status');

        $response->assertStatus(503);
        $checks = $response->json('data.checks');
        self::assertIsArray($checks);
        self::assertIsArray($checks['database']);
        self::assertFalse($checks['database']['ok']);
        self::assertSame('check failed', $checks['database']['message']);
    }

    /**
     * @throws OverflowException
     */
    public function testStatusHealthCheckFailureExposesMessageWhenDebugOn(): void
    {
        $message = fake()->unique()->sentence();
        config([
            'laravel-controller.status.checks' => ['database'],
            'app.debug' => true,
        ]);

        $connection = Mockery::mock();
        $connection->shouldReceive('getPdo')->once()->andThrow(new RuntimeException($message));
        DB::shouldReceive('connection')->once()->andReturn($connection);

        $response = $this->getJson('/api/v1/status');

        $response->assertStatus(503);
        $checks = $response->json('data.checks');
        self::assertIsArray($checks);
        self::assertIsArray($checks['database']);
        self::assertSame($message, $checks['database']['message']);
    }

    public function testStatusCacheReadWriteFailureIsReported(): void
    {
        config([
            'laravel-controller.status.checks' => ['cache'],
        ]);

        /** @var MockInterface&CacheRepository $cache */
        $cache = Mockery::mock(CacheRepository::class);
        $cache->shouldReceive('put')->once()->andReturn(true);
        $cache->shouldReceive('get')->once()->andReturn(null);
        $this->app()->instance(CacheRepository::class, $cache);

        $response = $this->getJson('/api/v1/status');

        $response->assertStatus(503);
        $checks = $response->json('data.checks');
        self::assertIsArray($checks);
        self::assertIsArray($checks['cache']);
        self::assertFalse($checks['cache']['ok']);
        self::assertSame('read/write failed', $checks['cache']['message']);
    }

    public function testStatusIgnoresNonArrayChecksAndInvalidTimeout(): void
    {
        config([
            'laravel-controller.status' => [
                'checks' => 'not-an-array',
                'checks_timeout_seconds' => 'soon',
            ],
        ]);

        $response = $this->getJson('/api/v1/status');

        $response->assertOk();
        $data = $response->json('data');
        self::assertIsArray($data);
        self::assertArrayNotHasKey('checks', $data);
    }

    public function testStatusUsesLaravelVersionWhenAppVersionIsNotString(): void
    {
        config([
            'laravel-controller.status.include_version' => true,
            'app.version' => 12345,
        ]);

        $response = $this->getJson('/api/v1/status');

        $response->assertOk();
        $version = $response->json('data.version');
        self::assertIsString($version);
        self::assertNotSame('12345', $version);
    }

    public function testStatusTreatsNonArrayStatusConfigAsEmpty(): void
    {
        config(['laravel-controller.status' => 'invalid']);

        $response = $this->getJson('/api/v1/status');

        $response->assertOk();
        $data = $response->json('data');
        self::assertIsArray($data);
        self::assertArrayNotHasKey('version', $data);
        self::assertArrayNotHasKey('checks', $data);
    }

    public function testStatusMarksSubsequentChecksTimedOut(): void
    {
        $checker = new SlowStatusHealthChecker();

        /** @var array<string, array{ok: bool, message?: string}> $results */
        $results = $checker->run(['slow', 'cache'], 1);

        self::assertTrue($results['slow']['ok']);
        self::assertFalse($results['cache']['ok']);
        self::assertArrayHasKey('message', $results['cache']);
        self::assertSame('timeout', $results['cache']['message']);
    }

    public function testStatusRunsCustomStatusHealthCheckClass(): void
    {
        config([
            'laravel-controller.status.checks' => [FakeStatusHealthCheck::class],
            'laravel-controller.status.checks_timeout_seconds' => 5,
        ]);

        $response = $this->getJson('/api/v1/status');

        $response->assertOk();
        $checks = $response->json('data.checks');
        self::assertIsArray($checks);
        self::assertArrayHasKey('fake_probe', $checks);
        self::assertIsArray($checks['fake_probe']);
        self::assertTrue($checks['fake_probe']['ok']);
    }

    private function app(): Application
    {
        self::assertNotNull($this->app);

        return $this->app;
    }
}
