# laravel-model-json-storage

[![Source Code](https://img.shields.io/badge/source-okipa/laravel--model--json--storage-blue.svg)](https://github.com/Okipa/laravel-model-json-storage)
[![Latest Version](https://img.shields.io/github/release/okipa/laravel-model-json-storage.svg?style=flat-square)](https://github.com/Okipa/laravel-model-json-storage/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/okipa/laravel-model-json-storage.svg?style=flat-square)](https://packagist.org/packages/okipa/laravel-model-json-storage)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Build Status](https://scrutinizer-ci.com/g/Okipa/laravel-model-json-storage/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Okipa/laravel-model-json-storage/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/Okipa/laravel-model-json-storage/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Okipa/laravel-model-json-storage/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Okipa/laravel-model-json-storage/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Okipa/laravel-model-json-storage/?branch=master)

Storing your models in a json file rather than in database (single or few lines recording) can be a good option.  
This package saves you to create a table for a ridiculous amount of lines, improves the data recovery performances, and allows you to store and access to your models from a json files as you would do it in database.

------------------------------------------------------------------------------------------------------------------------

## To read before use
Please keep in mind that :
- The purpose of this package is to store your model instances in json.  
- Consequently, the json file is always entirely read when you access to your data
- Consequently, you should **NOT** use this package if you have a lot of instances to store, it could cause performance issues rather than improve it.
- All the query-related and model-related functionalities are not available (especially those which use database), this package has been made for quite simple use cases.
- This package enables you to manipulate models as if it they would been stored in database but it always uses the [Illuminate\Support\Collection methods](https://laravel.com/docs/5.4/collections) methods under the hood.

------------------------------------------------------------------------------------------------------------------------

## Installation
- Install the package with composer :
```bash
composer require okipa/laravel-model-json-storage
```

- Laravel 5.5+ uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.
If you don't use auto-discovery or if you use a Laravel 5.0- version, add the package service provider in the `register()` method from your `app/Providers/AppServiceProvider.php` :
```php
// laravel model json storage
// https://github.com/Okipa/laravel-model-json-storage
$this->app->register(Okipa\LaravelModelJsonStorage\ModelJsonStorageServiceProvider::class);
```

------------------------------------------------------------------------------------------------------------------------

## Usage
First, add the `ModelJsonStorage` trait in your model.

```php
class MyTestModel extends Illuminate\Database\Eloquent\Model
{
    use Okipa\LaravelModelJsonStorage\ModelJsonStorage;
    
    [...]
}
```

Then, just manipulate your model normally.  
After a storage, you will see a json file named with your model title in the path defined in the `model-json-storage` config file.

```php
$testModel = app(MyTestModel::class)->create([
    'name' => 'John Doe',
    'email' => 'john@doe.com',
    'password' => Hash::make('secret'),
]);
```

```php
$testModel = app(MyTestModel::class)->all();
```

```php
$testModel = app(MyTestModel::class)->where('email', 'john@doe.com')->first();
```

```php
$testModel->update([
    'name' => 'Gary Cook'
]);
```

```php
$testModel->delete();
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

------------------------------------------------------------------------------------------------------------------------

## API
The most used query-related and model-related methods have been overridden to allow you to use your json stored model as usual.  
Retrieve the list of the available methods bellow.  
However, if you want to add a method for your personal needs, do not hesitate to improve this package with a PR.

### Available Illuminate\Database\Eloquent\Model methods
- [save()](https://laravel.com/api/5.0/Illuminate/Database/Eloquent/Model.html#method_save)
- [update()](https://laravel.com/api/5.0/Illuminate/Database/Eloquent/Model.html#method_update)
- [delete()](https://laravel.com/api/5.0/Illuminate/Database/Eloquent/Model.html#method_delete)
- [all()](https://laravel.com/api/5.0/Illuminate/Database/Eloquent/Model.html#method_all)

### Available Illuminate\Database\Query\Builder methods
- [get()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_get)
- [Select()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_select)
- [addSelect()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_addSelect)
- [where()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_where)
- [whereNull()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_whereNull)
- [whereNotNull()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_whereNotNull)
- [orderBy()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_orderBy)
- [orderByDesc()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_orderByDesc)
- [whereIn()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_whereIn)
- [whereNotIn()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_whereNotIn)
- [find()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_find)
- [findOrFail()](https://laravel.com/api/5.0/Illuminate/Database/Eloquent/Builder.html#method_findOrFail)
- [paginate()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_paginate)
- [value()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_value)
- [pluck()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_pluck)
- [count()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_count)
- [min()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_min)
- [max()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_max)
- [avg()](https://laravel.com/api/5.0/Illuminate/Database/Query/Builder.html#method_avg)

### Available Illuminate\Database\Concerns\BuildsQueries methods
- [first()](https://laravel.com/api/5.0/Illuminate/Database/Concerns/BuildsQueries.html#method_first)
