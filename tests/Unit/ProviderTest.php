<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Unit;

use Illuminate\Foundation\Application;
use JOOservices\LaravelController\Providers\LaravelControllerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

final class ProviderTest extends OrchestraTestCase
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
        // Point base path to a directory that definitely does NOT have routes/api
        $app->setBasePath(sys_get_temp_dir());
    }

    public function testBootDoesNothingWhenRoutesDirectoryIsMissing(): void
    {
        // Provider boots in setUp; missing routes/api must not throw.
        self::assertFalse(is_dir(base_path('routes/api')));
        self::assertNotNull($this->app);
        self::assertTrue($this->app->bound('config'));
    }
}
