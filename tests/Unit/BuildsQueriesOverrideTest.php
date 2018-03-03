<?php

namespace Okipa\LaravelModelJsonStorage\Test\Unit;

use Models\UserJson;
use Okipa\LaravelModelJsonStorage\Models\UserDatabase;
use Okipa\LaravelModelJsonStorage\Test\helpers\CreateUsers;
use Tests\ModelJsonStorageTestCase;

class BuildsQueriesOverrideTest extends ModelJsonStorageTestCase
{
    use CreateUsers;

    public function setUp()
    {
        parent::setUp();
        $this->instanciateFaker();
    }

    public function testFirst()
    {
        $this->createMultipleDatabaseUsers(3);
        $firstDatabaseUsers = app(UserDatabase::class)->first();
        $firstJsonUsers = app(UserJson::class)->first();
        $this->assertEquals($firstDatabaseUsers->toArray(), $firstJsonUsers->toArray());
    }
}