<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use JOOservices\LaravelController\Providers\LaravelControllerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelControllerServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        // Point base path to the real project root so standard Laravel paths work
        $app->setBasePath(dirname(__DIR__));
    }

    /**
     * @return array<string, mixed>
     */
    protected function jsonPayload(JsonResponse $response): array
    {
        $data = $response->getData(true);
        self::assertIsArray($data);

        /** @var array<string, mixed> $data */
        return $data;
    }
}
