<?php

namespace Okipa\LaravelModelJsonStorage\Test\Unit;

use File;
use Hash;
use Okipa\LaravelModelJsonStorage\Test\Fakers\UsersFaker;
use Okipa\LaravelModelJsonStorage\Test\Models\UserJson;
use Okipa\LaravelModelJsonStorage\Test\Models\UserDatabase;
use Tests\ModelJsonStorageTestCase;

class ModelOverrideTest extends ModelJsonStorageTestCase
{
    use UsersFaker;

    public function setUp()
    {
        parent::setUp();
        $this->instanciateFaker();
    }

    public function testGetStoragePath()
    {
        $this->assertEquals(
            storage_path(config('model-json-storage.storage_path') . '/userjson.json'),
            app(UserJson::class)->getJsonStoragePath()
        );
    }

    public function testSave()
    {
        $usersCollection = $this->createUniqueDatabaseAndJsonUser();
        $userDatabase = $usersCollection->get('databaseUser');
        $userJson = $usersCollection->get('jsonUser');
        $newEmail = $this->faker->email;
        $userDatabase->email = $newEmail;
        $userDatabase->save();
        $userJson->email = $newEmail;
        $userJson->save();
        $this->assertEquals(
            app(UserDatabase::class)->find($userDatabase->id)->email,
            array_first(app(UserDatabase::class)->fromJson(File::get($userJson->getJsonStoragePath())))['email']
        );
    }

    public function testUpdate()
    {
        $usersCollection = $this->createUniqueDatabaseAndJsonUser();
        $userDatabase = $usersCollection->get('databaseUser');
        $userJson = $usersCollection->get('jsonUser');
        $newName = $this->faker->name;
        $userDatabase->update([
            'name' => $newName,
        ]);
        $userJson->update([
            'name' => $newName,
        ]);
        $this->assertEquals(
            app(UserDatabase::class)->find($userDatabase->id)->name,
            array_first(app(UserDatabase::class)->fromJson(File::get($userJson->getJsonStoragePath())))['name']
        );
    }

    public function testCreate()
    {
        $usersCollection = $this->createUniqueDatabaseAndJsonUser();
        $userDatabase = $usersCollection->get('databaseUser');
        $userJson = $usersCollection->get('jsonUser');
        $this->assertFileExists($userJson->getJsonStoragePath());
        $this->assertEquals(
            [app(UserDatabase::class)->find($userDatabase->id)->getAttributes()],
            app(UserDatabase::class)->fromJson(File::get($userJson->getJsonStoragePath()))
        );
    }

    public function testAll()
    {
        $this->createMultipleDatabaseUsers(3);
        $allDatabaseUsersArray = app(UserDatabase::class)->all()->toArray();
        $allJsonUsersArray = app(UserJson::class)->all()->toArray();
        $this->assertEquals($allDatabaseUsersArray, $allJsonUsersArray);
    }

    public function testDelete()
    {
        $usersCollection = $this->createMultipleDatabaseUsers(3);
        $firstDatabaseUser = $usersCollection->get('databaseUsers')->where('id', 1)->first();
        $firstJsonUser = $usersCollection->get('jsonUsers')->where('id', 1)->first();
        $databaseDeleteResult = $firstDatabaseUser->delete();
        $jsonDeleteResult = $firstJsonUser->delete();
        $allDatabaseUsersArray = app(UserDatabase::class)->all()->toArray();
        $allJsonUsersArray = app(UserJson::class)->all()->toArray();
        $this->assertEquals($databaseDeleteResult, $jsonDeleteResult);
        $this->assertEquals($allDatabaseUsersArray, $allJsonUsersArray);
    }
}
