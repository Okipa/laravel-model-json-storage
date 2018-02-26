<?php

namespace Okipa\LaravelModelJsonStorage;

use File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ModelJsonStorage
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $modelsFromJson;
    protected $wheres = [];
    protected $whereIns = [];
    protected $whereNotIns = [];
    protected $orderBys = [];

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
    protected function saveToJson(): Model
    {
        if ($this->{$this->primaryKey}) {
            $this->updateModel();
        } else {
            $this->createModel();
        }

        return $this->where($this->primaryKey, $this->{$this->primaryKey})->first();
    }

    /**
     * @return void
     */
    protected function updateModel(): void
    {
        if ($this->usesTimestamps()) {
            $this->setTimestampFields(true);
        }
        $withoutCurrentModel = $this->whereNotIn($this->primaryKey, [$this->getAttribute($this->primaryKey)])->get();
        $models = $withoutCurrentModel->push($this->makeVisible($this->getHidden()))->sortBy($this->primaryKey);
        File::put($this->getJsonStoragePath(), $models->toJson());
    }

    /**
     * @param bool $update
     *
     * @return void
     */
    protected function setTimestampFields($update = false): void
    {
        $now = $this->freshTimestampString();
        if (! $update) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
    }

    /**
     * @return string
     */
    public abstract function freshTimestampString();

    /**
     * @return array
     */
    public abstract function getAttribute();

    /**
     * @return void
     */
    public abstract function setCreatedAt();

    /**
     * @return void
     */
    public abstract function setUpdatedAt();

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
     * @return Collection
     */
    protected function getFromJson(): Collection
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
        $modelsArray = $this->getRawArrayFromJson();
        foreach ($modelsArray as $key => $modelArray) {
            $modelsArray[$key] = app($this->getMorphClass())
                ->setRawAttributes($modelArray)
                ->makeVisible($this->getHidden());
        }
        $this->modelsFromJson = collect($modelsArray);
    }

    /**
     * @return array
     */
    protected function getRawArrayFromJson(): array
    {
        $modelsArray = [];
        if (file_exists($this->getJsonStoragePath())) {
            $modelsArray = $this->fromJson(File::get($this->getJsonStoragePath()));
        }

        return $modelsArray;
    }

    /**
     * @return string
     */
    protected function getJsonStoragePath(): string
    {
        $modelName = str_slug(last(explode('\\', $this->getMorphClass())));
        $configStoragePath = storage_path(config('model-json-storage.storage_path'));
        if (! is_dir($configStoragePath)) {
            mkdir($configStoragePath, 0777, true);
        }
        $jsonStoragePath = $configStoragePath . '/' . $modelName . '.json';

        return $jsonStoragePath;
    }

    /**
     * @return string
     */
    public abstract function getMorphClass();

    /**
     * @return mixed
     */
    public abstract function fromJson();

    /**
     * @return array
     */
    public abstract function getHidden();

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
     * @return Model
     */
    public abstract function makeVisible();

    /**
     * @return void
     */
    protected function createModel(): void
    {
        $this->setModelPrimaryKeyValue();
        if ($this->usesTimestamps()) {
            $this->setTimestampFields();
        }
        $models = $this->all()->push($this->makeVisible($this->getHidden()));
        File::put($this->getJsonStoragePath(), $models->toJson());
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
     * @return Model
     */
    public abstract function getAttributes();

    /**
     * @return Model
     */
    public abstract function setRawAttributes();

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
     * @param array $attributes
     * @param array $options
     *
     * @return mixed
     */
    public function update(array $attributes = [], array $options = [])
    {
        return $this->fill($attributes)->save($options);
    }

    /**
     * @return Model
     */
    public abstract function fill();

    /**
     * @return bool
     */
    public function delete(): bool
    {
        $this->deleteModel();

        return true;
    }

    /**
     * @return void
     */
    protected function deleteModel(): void
    {
        $withoutCurrentModel = $this->whereNotIn($this->primaryKey, [$this->getAttribute($this->primaryKey)])->get();
        File::put($this->getJsonStoragePath(), $withoutCurrentModel->toJson());
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
     * @return int
     */
    public function max($column): int
    {
        return $this->get()->max($column);
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function min($column): int
    {
        return $this->get()->min($column);
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function avg($column): int
    {
        return $this->get()->avg($column);
    }

    /**
     * @param $column
     *
     * @return Collection
     */
    public function distinct($column): Collection
    {
        return $this->get()->unique($column);
    }

    /**
     * @param $column
     *
     * @return Collection
     */
    public function groupBy($column): Collection
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