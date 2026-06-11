<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // docker-compose injects DB_CONNECTION=pgsql as a system env var that
        // Dotenv's ImmutableAdapter cannot override. Override config directly
        // after app creation so RefreshDatabase targets SQLite in-memory instead
        // of wiping the real PostgreSQL database on every test run.
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('cache.default', 'array');

        return $app;
    }
}
