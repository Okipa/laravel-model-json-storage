<?php

namespace Okipa\LaravelModelJsonStorage\Test;

use Faker\Factory;
use File;
use Orchestra\Testbench\TestCase;

abstract class ModelJsonStorageTestCase extends TestCase
{
    public $faker;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('model-json-storage.storage_path', 'app/json');
    }

    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path'     => realpath(__DIR__ . '/database/migrations'),
        ]);
        File::deleteDirectory(storage_path(config('model-json-storage.storage_path')));
        $this->faker = Factory::create();
    }
}
