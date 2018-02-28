<?php

namespace Tests\Unit;

use Hash;
use Okipa\LaravelModelJsonStorage\Models\UserDatabase;
use Tests\ModelJsonStorageTestCase;

class UserTest extends ModelJsonStorageTestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
        $userDatabase = app(UserDatabase::class)->create([
            'name'     => 'test',
            'email'    => 'test',
            'password' => Hash::make('secret'),
        ]);
        dd($userDatabase->all()->toArray());
    }
}
