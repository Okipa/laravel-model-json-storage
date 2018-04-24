<?php

namespace Okipa\LaravelModelJsonStorage\Test\Fakers;

use Faker\Factory;
use Hash;
use Illuminate\Support\Collection;
use Okipa\LaravelModelJsonStorage\Test\Models\UserJson;
use Okipa\LaravelModelJsonStorage\Test\Models\UserDatabase;

trait UsersFaker
{
    public $faker;
    public $clearPassword;
    public $data;

    public function instanciateFaker()
    {
        $this->faker = Factory::create();
    }

    public function createUniqueDatabaseAndJsonUser()
    {
        $fakeUserData = $this->generateFakeUserData();
        $databaseUser = $this->createUniqueDatabaseUser($fakeUserData);
        $jsonUser = $this->createUniqueJsonUser($fakeUserData);

        return collect([
            'databaseUser' => $databaseUser,
            'jsonUser'     => $jsonUser,
        ]);
    }

    public function generateFakeUserData()
    {
        $this->clearPassword = $this->faker->password;

        return [
            'name'     => $this->faker->name,
            'email'    => $this->faker->email,
            'password' => Hash::make($this->clearPassword),
        ];
    }

    public function createUniqueDatabaseUser(array $data)
    {
        $databaseUser = app(UserDatabase::class)->create($data);

        return app(UserDatabase::class)->find($databaseUser->id);
    }

    public function createUniqueJsonUser(array $data)
    {
        return app(UserJson::class)->create($data);
    }

    public function createMultipleDatabaseUsers(int $count)
    {
        $databaseUsers = new Collection();
        $jsonUsers = new Collection();
        foreach ($this->generateFakeUsersData($count) as $fakeUserData) {
            $databaseUsers->push($this->createUniqueDatabaseUser($fakeUserData));
            $jsonUsers->push($this->createUniqueJsonUser($fakeUserData));
        }

        return collect([
            'databaseUsers' => $databaseUsers,
            'jsonUsers'     => $jsonUsers,
        ]);
    }

    public function generateFakeUsersData(int $count)
    {
        $data = [];
        for ($ii = 0; $ii < $count; $ii++) {
            $data[] = [
                'name'     => $this->faker->name,
                'email'    => $this->faker->email,
                'password' => Hash::make($this->clearPassword),
            ];
        }

        return $data;
    }
}
