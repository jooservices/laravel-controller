<?php

namespace Tests;

use JOOservices\LaravelController\Providers\LaravelControllerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelControllerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Point base path to the real project root so standard Laravel paths work
        $app->setBasePath(dirname(__DIR__));
    }
}
