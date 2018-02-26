# laravel-model-json-storage
Storing your models in a json file rather than in database (single or few lines recording) can be a good option.  
This package saves you to create a table for a ridiculous amount of lines, improves the data recovery performances, and allows you to store and access to your models from a json files as you would do it in database.

------------------------------------------------------------------------------------------------------------------------

## To read before use
Keep in mind that with this package, the stored models are always extracted from a json file and not from a database (the purpose of this package).  
To do so, the json file is always entirely read when you access to your data.  
Also :
- You should not use this package if you have a lot of objects to store, it could cause performance issues rather than improve it.
- All the Eloquent functionalities are not available (especially those which use database), this package has been made for simple use cases.
- This package enables you to manipulate models as if it they would been stored in database but it always uses the [Illuminate\Support\Collection methods](https://laravel.com/docs/5.6/collections) methods under the hood.

------------------------------------------------------------------------------------------------------------------------

## Installation
- Install the package with composer :
```bash
composer require okipa/laravel-model-json-storage
```

- Laravel 5.5+ uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.
If you don't use auto-discovery or if you use a Laravel 5.4- version, add the package service provider in the `register()` method from your `app/Providers/AppServiceProvider.php` :
```php
// laravel model json storage
// https://github.com/Okipa/laravel-model-json-storage
$this->app->register(Okipa\LaravelModelJsonStorage\ModelJsonStorageServiceProvider::class);
```

------------------------------------------------------------------------------------------------------------------------

## Usage
First, add the `ModelJsonStorage` trait in your model.

```php
class Test extends Authenticatable
{
    use ModelJsonStorage;
    
    [...]
}
```

Then, just manipulate your model normally.  
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

------------------------------------------------------------------------------------------------------------------------

## Customize configuration
To personalize the package configuration, you have to publish it first with the following script :
```bash
php artisan vendor:publish --tag=model-json-storage::config
```
Then, open the published package configuration file (`config/model-json-storage.php`) and override the default configuration by setting your own values for the following items :
- json storage path
- ... that's all for now.
