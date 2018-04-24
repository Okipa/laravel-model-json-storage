<?php

namespace Okipa\LaravelModelJsonStorage\Test\Unit;


use Okipa\LaravelModelJsonStorage\Test\Fakers\UsersFaker;
use Okipa\LaravelModelJsonStorage\Test\Models\UserDatabase;
use Okipa\LaravelModelJsonStorage\Test\Models\UserJson;
use Tests\ModelJsonStorageTestCase;

class BuilderOverrideTest extends ModelJsonStorageTestCase
{
    use UsersFaker;

    public function setUp()
    {
        parent::setUp();
    }

    public function testGet()
    {
        $this->createMultipleDatabaseUsers(3);
        $getDatabaseUsers = app(UserDatabase::class)->get();
        $getJsonUsers = app(UserJson::class)->get();
        $getIdAndEmailDatabaseUsers = app(UserDatabase::class)->get(['id', 'email']);
        $getIdAndEmailJsonUsers = app(UserJson::class)->get(['id', 'email']);
        $this->assertEquals($getDatabaseUsers->toArray(), $getJsonUsers->toArray());
        // notice : we compare the models one by one because query builder does return objects from get() in a random way
        $getIdAndEmailDatabaseUsers->each(function($databaseUser) use ($getIdAndEmailJsonUsers) {
            $jsonUserToCompare = $getIdAndEmailJsonUsers->where('id', $databaseUser->id)->first();
            $this->assertEquals($databaseUser->toArray(), $jsonUserToCompare->toArray());
        });
    }

    public function testSelect()
    {
        $this->createMultipleDatabaseUsers(3);
        $selectedDatabaseUser = app(UserDatabase::class)->select('name')->get();
        $selectedJsonUser = app(UserJson::class)->select('name')->get();
        $this->assertEquals($selectedDatabaseUser->toArray(), $selectedJsonUser->toArray());
    }

    public function testAddSelect()
    {
        $this->createMultipleDatabaseUsers(3);
        $selectedDatabaseUser = app(UserDatabase::class)->select('id')->addSelect('name')->get();
        $selectedJsonUser = app(UserJson::class)->select('id')->addSelect('name')->get();
        $this->assertEquals($selectedDatabaseUser->toArray(), $selectedJsonUser->toArray());
    }

    public function testWhere()
    {
        $this->createMultipleDatabaseUsers(3);
        $whereDatabaseUsers = app(UserDatabase::class)->where('id', 2)->get();
        $whereJsonUsers = app(UserJson::class)->where('id', 2)->get();
        $this->assertEquals($whereDatabaseUsers->toArray(), $whereJsonUsers->toArray());
    }

    public function testWhereIn()
    {
        $this->createMultipleDatabaseUsers(3);
        $whereInDatabaseUsers = app(UserDatabase::class)->whereIn('id', [2, 3])->get();
        $whereInJsonUsers = app(UserJson::class)->whereIn('id', [2, 3])->get();
        $this->assertEquals($whereInDatabaseUsers->toArray(), $whereInJsonUsers->toArray());
    }

    public function testWhereNotIn()
    {
        $this->createMultipleDatabaseUsers(3);
        $whereInDatabaseUsers = app(UserDatabase::class)->whereNotIn('id', [1, 3])->get();
        $whereInJsonUsers = app(UserJson::class)->whereNotIn('id', [1, 3])->get();
        $this->assertEquals($whereInDatabaseUsers->toArray(), $whereInJsonUsers->toArray());
    }

    public function testOrderBy()
    {
        $this->createMultipleDatabaseUsers(3);
        $orderedByIdDescDatabaseUsers = app(UserDatabase::class)->orderBy('id', 'desc')->get();
        $orderedByIdDescJsonUsers = app(UserJson::class)->orderBy('id', 'desc')->get();
        $orderedByNameAscDatabaseUsers = app(UserDatabase::class)->orderBy('name', 'asc')->get();
        $orderedByNameAscJsonUsers = app(UserJson::class)->orderBy('name', 'asc')->get();
        $this->assertEquals($orderedByIdDescDatabaseUsers->toArray(), $orderedByIdDescJsonUsers->toArray());
        $this->assertEquals($orderedByNameAscDatabaseUsers->toArray(), $orderedByNameAscJsonUsers->toArray());
    }

    public function testFind()
    {
        $this->createMultipleDatabaseUsers(5);
        $foundDatabaseUser = app(UserDatabase::class)->find(4);
        $foundJsonUser = app(UserJson::class)->find(4);
        $this->assertEquals($foundDatabaseUser->toArray(), $foundJsonUser->toArray());
    }

    public function testPaginate()
    {
        $this->createMultipleDatabaseUsers(10);
        $firstPageDatabaseUser = app(UserDatabase::class)->paginate();
        $firstPageJsonUser = app(UserJson::class)->paginate();
        $secondPageDatabaseUser = app(UserDatabase::class)->paginate(5, ['name'], 'page', 2);
        $secondPageJsonUser = app(UserJson::class)->paginate(5, ['name'], 'page', 2);
        $this->assertEquals($firstPageDatabaseUser->toArray(), $firstPageJsonUser->toArray());
        $this->assertEquals($secondPageDatabaseUser->toArray(), $secondPageJsonUser->toArray());
    }

    public function testValue()
    {
        $this->createMultipleDatabaseUsers(3);
        $valueDatabaseUser = app(UserDatabase::class)->where('id', 2)->value('email');
        $valueUserJson = app(UserJson::class)->where('id', 2)->value('email');
        $this->assertEquals($valueDatabaseUser, $valueUserJson);
    }

    public function testPluck()
    {
        $this->createMultipleDatabaseUsers(3);
        $countDatabaseUser = app(UserDatabase::class)->pluck('name', 'email');
        $countUserJson = app(UserJson::class)->pluck('name', 'email');
        $this->assertEquals($countDatabaseUser, $countUserJson);
    }

    public function testCount()
    {
        $this->createMultipleDatabaseUsers(10);
        $countDatabaseUser = app(UserDatabase::class)->count();
        $countUserJson = app(UserJson::class)->count();
        $this->assertEquals($countDatabaseUser, $countUserJson);
    }

    public function testMin()
    {
        $this->createMultipleDatabaseUsers(10);
        $minDatabaseUser = app(UserDatabase::class)->min('id');
        $minUserJson = app(UserJson::class)->min('id');
        $this->assertEquals($minDatabaseUser, $minUserJson);
    }

    public function testMax()
    {
        $this->createMultipleDatabaseUsers(10);
        $maxDatabaseUser = app(UserDatabase::class)->max('id');
        $maxUserJson = app(UserJson::class)->max('id');
        $this->assertEquals($maxDatabaseUser, $maxUserJson);
    }

    public function testAvg()
    {
        $this->createMultipleDatabaseUsers(10);
        $avgDatabaseUser = app(UserDatabase::class)->avg('id');
        $avgUserJson = app(UserJson::class)->avg('id');
        $this->assertEquals($avgDatabaseUser, $avgUserJson);
    }
}
