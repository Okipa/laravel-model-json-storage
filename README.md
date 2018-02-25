# PACKAGE IN DEVELOPMENT

# laravel-model-json-storage
It is sometimes a better option to store your models in a json file rather than in database (in case of a single line, or for a few line recordings).  
This package prevents you to create a table for a ridiculous amount of lines and improves the data recovery performances.  
With it, store and access to your models data from a json files as you would do it in database.

## Disclaimer
Keep in mind that with this package, the model data is always extracted from a json file and not from a database (as it is the purpose of this package).  
As so, the whole json file is always read when you get access to your data (read once, but entirely).  
Please remember that :
- You should not use this package if you have a lot of objects to store, it could cause performance issues rather than improve it.
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
After a storage, you will see a json file named with your model title in the path defined in the `model-json-storage` config file.

```php
$user = app(User::class)->create([
    'name' => 'John Doe',
    'email' => 'john@doe.com',
    'password' => Hash::make('secret'),
]);
```

```php
$model = app(User::class)->all();
```

```php
$model = app(User::class)->where('email', 'john@doe.com')->first();
```

```php
$model->update([
    'name' => 'Gary Cook'
]);
```

```php
$model->delete();
```

## Customize json storage path
To personalize the package configuration, you have to publish it first with the following script :
```bash
php artisan vendor:publish --tag=model-json-storage::config
```