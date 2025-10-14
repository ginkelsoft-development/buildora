<?php

namespace Ginkelsoft\Buildora\Tests;

use Ginkelsoft\Buildora\Providers\BuildoraServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup if needed
    }

    protected function getPackageProviders($app): array
    {
        return [
            BuildoraServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup Buildora config
        $app['config']->set('buildora.route_prefix', 'buildora');
        $app['config']->set('buildora.models_namespace', 'App\\Models\\');
    }
}
