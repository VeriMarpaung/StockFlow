<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * docker-compose injects DB_CONNECTION=pgsql, CACHE_STORE=redis, etc. as real
     * OS env vars. Dotenv's immutable repository (and phpunit.xml's force env) cannot
     * override an OS-level env var, so config() resolves to the live pgsql/redis.
     *
     * Forcing the config here — in refreshApplication(), which setUp() calls *before*
     * RefreshDatabase migrates — guarantees tests run against an isolated SQLite
     * in-memory DB + array cache, instead of wiping the real PostgreSQL database and
     * polluting the real Redis cache on every run.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        $this->app['config']->set('cache.default', 'array');
        $this->app['config']->set('queue.default', 'sync');
        $this->app['config']->set('session.driver', 'array');
    }
}
