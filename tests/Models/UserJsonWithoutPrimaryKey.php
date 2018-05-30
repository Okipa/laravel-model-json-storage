<?php

namespace Okipa\LaravelModelJsonStorage\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Okipa\LaravelModelJsonStorage\ModelJsonStorage;

class UserJsonWithoutPrimaryKey extends Model
{
    use ModelJsonStorage;
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = null;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
