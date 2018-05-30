<?php

namespace Okipa\LaravelModelJsonStorage\Test\Unit;

use Okipa\LaravelModelJsonStorage\Test\Fakers\UsersFaker;
use Okipa\LaravelModelJsonStorage\Test\ModelJsonStorageTestCase;
use Okipa\LaravelModelJsonStorage\Test\Models\UserJson;
use Okipa\LaravelModelJsonStorage\Test\Models\UserDatabase;

class BuildsQueriesOverrideTest extends ModelJsonStorageTestCase
{
    use UsersFaker;

    public function testFirst()
    {
        $this->createMultipleDatabaseUsers(3);
        $firstDatabaseUsers = app(UserDatabase::class)->first();
        $firstJsonUsers = app(UserJson::class)->first();
        $this->assertEquals($firstDatabaseUsers->toArray(), $firstJsonUsers->toArray());
    }
}
