# laravel-model-json-storage

[![Source Code](https://img.shields.io/badge/source-okipa/laravel--model--json--storage-blue.svg)](https://github.com/Okipa/laravel-model-json-storage)
[![Latest Version](https://img.shields.io/github/release/okipa/laravel-model-json-storage.svg?style=flat-square)](https://github.com/Okipa/laravel-model-json-storage/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/okipa/laravel-model-json-storage.svg?style=flat-square)](https://packagist.org/packages/okipa/laravel-model-json-storage)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
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

### Overridden Illuminate\Database\Eloquent\Model methods
Official API : https://laravel.com/api/5.4/Illuminate/Database/Eloquent/Model.html

#### save()
```php
/**
 * Save the model to the json file.
 *
 * @param  array $options
 *
 * @return bool
 */
public function save(array $options = [])
```

#### update()
```php
/**
 * Update the model in the json file.
 *
 * @param array $attributes
 * @param array $options
 *
 * @return mixed
 */
public function update(array $attributes = [], array $options = [])
```

#### delete()
```php
/**
 * Delete the model from the json file.
 *
 * @return bool|null
 * @throws Exception
 */
public function delete()
```

#### all()
```php
/**
 * Get all of the models from the json file.
 *
 * @param array $columns
 *
 * @return Collection
 */
public static function all($columns = ['*'])
```

### Overridden Illuminate\Database\Query\Builder methods
Official API : https://laravel.com/api/5.4/Illuminate/Database/Query/Builder.html

#### select()
```php
/**
* Set the column to be selected.
* Collection method used under the hood : only() : https://laravel.com/docs/5.4/collections#method-only
*
* @param  string $column
*
* @return Model
*/
public function select(string $column)
```

#### addSelect()
```php
/**
 * Add a new select column to the query.
 * Collection method used under the hood : only() : https://laravel.com/docs/5.4/collections#method-only
 *
 * @param  string $column
 *
 * @return Model
 */
public function addSelect(string $column)
```

#### where()
```php
/**
 * Add a basic where clause to the query.
 * Collection method used under the hood : where() : https://laravel.com/docs/5.4/collections#method-where
 *
 * @param  string $column
 * @param  string $operator
 * @param  mixed  $value
 *
 * @return Model
 */
public function where(string $column, string $operator = null, mixed $value = null)
```

#### whereIn()
```php
/**
 * Add a "where in" clause to the query.
 * Collection method used under the hood : whereIn() : https://laravel.com/docs/5.4/collections#method-wherein
 *
 * @param string $column
 * @param array  $values
 *
 * @return $this
 */
public function whereIn(string $column, array $values)
```

#### whereNotIn()
```php
/**
 * Add a "where in" clause to the query.
 * Collection method used under the hood : whereNotIn() : https://laravel.com/docs/5.4/collections#method-wherenotin
 *
 * @param string $column
 * @param array  $values
 *
 * @return $this
 */
public function whereIn(string $column, array $values)
```

#### orderBy()
```php
/**
 * Add an "order by" clause to the query.
 * Collection method used under the hood : sortBy() : https://laravel.com/docs/5.4/collections#method-sortby
 *
 * @param  string $column
 * @param  string $direction
 *
 * @return $this
 */
public function orderBy(string $column, string $direction = 'asc')
```

#### groupBy()
```php
/**
 * Add a "group by" clause to the query.
 * Collection method used under the hood : unique() : https://laravel.com/docs/5.4/collections#method-unique
 *
 * @param $column
 *
 * @return Collection
 */
public function groupBy($column)
```

#### distinct()
```php
/**
 * Force the query to only return distinct results.
 * Collection method used under the hood : unique() : https://laravel.com/docs/5.4/collections#method-unique
 *
 * @return Collection
 */
public function distinct(string $column)
```

#### get()
```php
/**
 * Execute the query as a "select" statement.
 *
 * @param  array $columns
 *
 * @return Collection
 */
public function get(array $columns = ['*'])
```

#### find()
```php
/**
 * Execute a query for a single record by ID.
 *
 * @param  int   $id
 * @param  array $columns
 *
 * @return mixed|static
 */
public function find(int $id, array $columns = ['*'])
```

#### pluck()
```php
/**
 * Get an array with the values of a given column.
 *
 * @param string $column
 * @param string $key
 *
 * @return Collection
 */
public function pluck(string $column, string $key = null)
```

#### min()
```php
/**
 * Retrieve the minimum value of a given column.
 *
 * @param $column
 *
 * @return int
 */
public function min(string $column)
```

#### max()
```php
/**
 * Retrieve the maximum value of a given column.
 *
 * @param  string $column
 *
 * @return int
 */
public function max(string $column)
```

#### avg()
```php
/**
 * Retrieve the average of the values of a given column.
 *
 * @param  string $column
 *
 * @return int
 */
public function avg(string $column)
```

#### count()
```php
/**
 * Retrieve the "count" result of the query.
 *
 * @param  array $columns
 *
 * @return int
 */
public function count(array $columns = ['*'])
```

#### paginate()
```php
/**
 * Paginate the given query.
 *
 * @param  int      $perPage
 * @param  int|null $page
 * @param array     $options
 *
 * @return LengthAwarePaginator
 */
public function paginate($perPage = null, $page = null, $options = [])
```

### Overridden Illuminate\Database\Concerns\BuildsQueries methods
Official API : https://laravel.com/api/5.4/Illuminate/Database/Concerns/BuildsQueries.html

#### chunk()
```php
/**
 * Chunk the results of the query.
 *
 * @param  int $count
 *
 * @return Collection
 */
public function chunk(int $count)
```

#### first()
```php
/**
 * Execute the query and get the first result.
 * Collection method used under the hood : first() : https://laravel.com/docs/5.4/collections#method-first
 *
 * @param  array $columns
 *
 * @return Model|null
 */
public function first(array $columns = ['*'])
```
