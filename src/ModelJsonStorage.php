<?php

namespace App;

use Carbon\Carbon;
use File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ModelJsonStorage
{
    protected $modelsFromJson;
    protected $wheres = [];
    protected $whereIns = [];
    protected $whereNotIns = [];
    protected $orderBys = [];

    /**
     * @param array $columns
     *
     * @return Collection
     */
    public static function all($columns = []): Collection
    {
        return (new static)->getFromJson();
    }

    /**
     * @return Collection
     */
    public function getFromJson(): Collection
    {
        if (! $this->modelsFromJson) {
            $this->loadJsonModels();
        }

        return $this->modelsFromJson;
    }

    /**
     * @return void
     */
    protected function loadJsonModels(): void
    {
        // we get the raw json content
        $modelsArray = $this->getRawArrayFromJson();
        // we instantiate each object from its array
        foreach ($modelsArray as $key => $modelArray) {
            $modelsArray[$key] = app($this->getMorphClass())
                ->setRawAttributes($modelArray)
                ->makeVisible($this->getHidden());
        }
        // we set the modelsFromJson variable
        $this->modelsFromJson = collect($modelsArray);
    }

    /**
     * @return array
     */
    protected function getRawArrayFromJson(): array
    {
        // we get an array from the json content
        $modelsArray = [];
        if (file_exists($this->getJsonStoragePath())) {
            $modelsArray = json_decode(File::get($this->getJsonStoragePath()), true);
        }

        return $modelsArray;
    }

    /**
     * @return string
     */
    protected function getJsonStoragePath(): string
    {
        // we get the model name
        $modelName = str_slug(last(explode('\\', $this->getMorphClass())));
        // we get the configured storage path
        $configStoragePath = storage_path(config('model-json-storage.storage_path'));
        // we create the path if it does not exists
        if (! is_dir($configStoragePath)) {
            mkdir($configStoragePath, 0777, true);
        }
        // we set the storage path
        $storagePath = $configStoragePath . '/' . $modelName . '.json';

        return $storagePath;
    }

    /**
     * @param string     $column
     * @param string     $operator
     * @param mixed|null $value
     *
     * @return $this
     */
    public function where(string $column, string $operator, mixed $value = null)
    {
        if (! isset($value)) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = compact('column', 'operator', 'value');

        return $this;
    }

    /**
     * @param string $column
     * @param array  $values
     *
     * @return $this
     */
    public function whereIn(string $column, array $values)
    {
        $this->whereIns[] = compact('column', 'values');

        return $this;
    }

    /**
     * @param string $column
     * @param array  $values
     *
     * @return $this
     */
    public function whereNotIn(string $column, array $values)
    {
        $this->whereNotIns[] = compact('column', 'values');

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'asc')
    {
        $this->orderBys[] = compact('column', 'direction');

        return $this;
    }

    /**
     * @param array $options
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function save(array $options = []): Model
    {
        return $this->saveToJson();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function saveToJson(): Model
    {
        if ($this->{$this->primaryKey}) {
            // we set the timestamp fields
            if ($this->timestamps) {
                dd('update', $this);
                $this->setTimestampFields(true);
                // todo : update
            }
        } else {
            // we set the model primary key value
            $this->setModelPrimaryKeyValue();
            // we set the timestamp fields
            if ($this->timestamps) {
                $this->setTimestampFields();
            }
            $models = $this->getFromJson()->push($this->makeVisible($this->getHidden()));
            File::put($this->getJsonStoragePath(), $models->toJson());
        }

        return $this;
    }
    
    public function delete()
    {
        // todo : delete
    }

    /**
     * @param bool $update
     *
     * @return void
     */
    protected function setTimestampFields($update = false): void
    {
        $now = Carbon::now()->toDateTimeString();
        if (! $update) {
            $this->created_at = $now;
        }
        $this->updated_at = $now;
    }

    /**
     * @return void
     */
    protected function setModelPrimaryKeyValue(): void
    {
        // we set the model primary key value according to the models already stored in the json file
        $modelPrimaryKeyValue = 1;
        if (! $this->getFromJson()->isEmpty()) {
            $lastModelId = $this->getFromJson()->sortBy('id')->last()->getAttribute($this->primaryKey);
            $modelPrimaryKeyValue = $lastModelId + 1;
        }
        // we add the primary key to the model attributes
        $attributes = array_merge([$this->primaryKey => $modelPrimaryKeyValue], $this->getAttributes());
        $this->setRawAttributes($attributes);
    }

    /**
     * @param string $column
     *
     * @return Collection
     */
    public function value(string $column): Collection
    {
        return $this->get()->pluck($column);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function get(): Collection
    {
        $modelsCollection = $this->getFromJson();
        foreach ($this->wheres as $where) {
            $modelsCollection = $modelsCollection->where($where['column'], $where['operator'], $where['value']);
        }
        foreach ($this->whereIns as $whereIn) {
            $modelsCollection = $modelsCollection->whereIn($whereIn['column'], $whereIn['values']);
        }
        foreach ($this->whereNotIns as $whereNotIn) {
            $modelsCollection = $modelsCollection->whereNotIn($whereNotIn['column'], $whereNotIn['values']);
        }
        foreach ($this->orderBys as $orders) {
            $modelsCollection = $modelsCollection->sortBy(
                $orders['column'],
                SORT_REGULAR,
                $orders['direction'] === 'desc'
            );
        }

        return $modelsCollection;
    }

    /**
     * @param string $column
     * @param string $key
     *
     * @return Collection
     */
    public function pluck(string $column, string $key = null): Collection
    {
        return $this->get()->pluck($column, $key);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->get()->count();
    }

    /**
     * @param $column
     *
     * @return Collection
     */
    public function max($column): Collection
    {
        return $this->get()->max($column);
    }

    /**
     * @param $column
     *
     * @return Collection
     */
    public function min($column): Collection
    {
        return $this->get()->min($column);
    }

    /**
     * @param $column
     *
     * @return Collection
     */
    public function avg($column): Collection
    {
        return $this->get()->avg($column);
    }

    /**
     * @param $column
     *
     * @return Collection
     */
    public function distinct($column) : Collection
    {
        return $this->get()->unique($column);
    }

    /**
     * @param $column
     *
     * @return Collection
     */
    public function groupBy($column) : Collection
    {
        return $this->get()->unique($column);
    }
    
    public function select()
    {
        // Todo : not compatible
    }

    public function addSelect()
    {
        // Todo : not compatible
    }

    public function chunk()
    {
        // Todo : not compatible
    }

    /**
     * @return Model
     */
    public function first(): Model
    {
        return $this->get()->first();
    }
}