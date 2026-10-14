<?php

namespace Saccharine\Documents\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCase extends Orchestra
{
    use WithWorkbench;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // This ensures Laravel's core schema migrations (like users) are run if needed
        $this->loadLaravelMigrations();
    }

    /**
     * Define environment setup.
     *
     * Workbench reads package providers from `testbench.yaml`, so they are no
     * longer registered here. This hook only tunes runtime config for tests.
     */
    protected function getEnvironmentSetUp($app)
    {
        // Provide a standard 32-character base64 string for Laravel's encrypter
        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYvnkFmTNFjq+95U=');

        // Force all database connections to use memory
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Load the package's database migrations.
     *
     * Workbench additionally loads `workbench/database/migrations`, but the
     * package migrations are loaded by the DocumentsServiceProvider.
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}