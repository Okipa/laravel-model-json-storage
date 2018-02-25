# laravel-model-json-storage
It is sometimes a better option to store your models in a json file rather than in database (in case of a single line, or for a few line recordings).  
This package prevents you to create a table for a ridiculous amount of lines and improves the data recovery performances.  
With it, store and access to your models data from a json files as you would do it in database.

## Disclaimer
Keep in mind that the data is always extracted from a json file and not from a database (as it is the purpose of this package).  
As so, the whole json file is always read when you get access to your data (once but entirely read indeed).  
Please remember that :
- You should not use this package if you have a lot of objects to store, it would cause performance issues rather than improve it.
- This package enables you to manipulate models as if it they would been stored in database but it always uses the [Illuminate\Support\Collection methods](https://laravel.com/docs/5.6/collections) methods under the hood.

## Usage
First, add the ModelJsonStorage` trait in your model.

```php

<?php
    
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use ModelJsonStorage;
    
    [...]
```

Then, in your controller for example, just manipulate your model normally.

```php
$user = app(User::class)->create([
    'name' => 'test',
    'email' => 'test@test',
    'password' => Hash::make('password'),
]);
```

```php
$model = app(User::class)->all();
```

```php
$model = app(User::class)->where('id', 1)->first();
```

## Eloquent <=> Collection methods associations
The `Illuminate\Database\Query\Builder` and the `Illuminate\Support\Collection` share some methods, which is quite convenient.  
But as you will see, the name or the behaviour of these methods is not always exactly the same and some adaptations has been made to give you the opportunity to use the json-stored models methods almost as if you would manipulate and database-connected model.

If you are not at ease with this, keep in mind that you can get all your stored models by using the `get()` or `all()` methods and then manipulate the data with the [Illuminate\Support\Collection methods](https://laravel.com/docs/5.6/collections).

| Builder method | Collection method |
|-----------|-----------|
| all() | all() |
| where() | where() |
| whereIn() | whereIn() |
| whereNotIn() | whereNotIn() |
| orderBy() | sortBy() |
| first() | first() |
| pluck() | pluck() |
| count() | count() |
| max() | max() |
| min() | min() |
| avg() | avg() |
| distinct() | unique() |
| groupBy() | unique() |
| select() | XXX |
| addSelect() | XXX |
| chunk() | XXX |

## Customize json storage path
To personalize the package configuration, you have to publish it first with the following script :
```bash
php artisan vendor:publish --tag=model-json-storage::config
```