<?php

namespace Okipa\LaravelModelJsonStorage;

use File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ModelJsonStorage
{
    use QueryBuilderOverride;
    use BuildsQueriesOverride;
    use ModelOverride;
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The collection of models extracted from the json file.
     *
     * @var Collection
     */
    protected $modelsFromJson;
    /**
     * The "select" clauses of the query.
     *
     * @var array
     */
    protected $selects = [];
    /**
     * The "where" clauses of the query.
     *
     * @var array
     */
    protected $wheres = [];
    /**
     * The "whereIn" clauses of the query.
     *
     * @var array
     */
    protected $whereIns = [];
    /**
     * The "whereNotIn" clauses of the query.
     *
     * @var array
     */
    protected $whereNotIns = [];
    /**
     * The "orderBy" clauses of the query.
     *
     * @var array
     */
    protected $orderBys = [];

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array $attributes
     *
     * @return Model
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public abstract function fill(array $attributes);

    /**
     * Save the model to the json file.
     *
     * @return bool
     */
    protected function saveToJson()
    {
        if ($this->{$this->primaryKey}) {
            $this->updateModelInJson();
        } else {
            $this->createModelInJson();
        }

        return true;
    }

    /**
     * Update the model in the json file.
     *
     * @return void
     */
    protected function updateModelInJson()
    {
        if ($this->usesTimestamps()) {
            $this->setTimestampFields(true);
        }
        $withoutCurrentModel = $this->whereNotIn($this->primaryKey, [$this->getAttribute($this->primaryKey)])->get();
        $models = $withoutCurrentModel->push($this->makeVisible($this->getHidden()))->sortBy($this->primaryKey);
        File::put($this->getJsonStoragePath(), $models->toJson());
    }

    /**
     * Determine if the model uses timestamps.
     *
     * @return bool
     */
    public abstract function usesTimestamps();

    /**
     * Set the model timestamp field
     *
     * @param bool $update
     *
     * @return void
     */
    protected function setTimestampFields($update = false)
    {
        $now = $this->freshTimestampString();
        if (! $update) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return string
     */
    public abstract function freshTimestampString();

    /**
     * Set the value of the "created at" attribute.
     *
     * @param  mixed $value
     *
     * @return Model
     */
    public abstract function setCreatedAt(mixed $value);

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed $value
     *
     * @return Model
     */
    public abstract function setUpdatedAt(mixed $value);

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public abstract function getAttribute(string $key);

    /**
     * Make the given, typically hidden, attributes visible.
     *
     * @param  array|string $attributes
     *
     * @return Model
     */
    public abstract function makeVisible(mixed $attributes);

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public abstract function getHidden();

    /**
     * Get the storage path for the model json file.
     *
     * @return string
     */
    protected function getJsonStoragePath()
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
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public abstract function getMorphClass();

    /**
     * Save a new model in the json file.
     *
     * @return void
     */
    protected function createModelInJson()
    {
        $this->setModelPrimaryKeyValue();
        if ($this->usesTimestamps()) {
            $this->setTimestampFields();
        }
        $models = $this->all()->push($this->makeVisible($this->getHidden()));
        File::put($this->getJsonStoragePath(), $models->toJson());
    }

    /**
     * Set the model primary key by incrementing from the bigger id found in the json file.
     *
     * @return void
     */
    protected function setModelPrimaryKeyValue(): void
    {
        $modelPrimaryKeyValue = 1;
        if (! $this->getFromJson()->isEmpty()) {
            $lastModelId = $this->getFromJson()->sortBy('id')->last()->getAttribute($this->primaryKey);
            $modelPrimaryKeyValue = $lastModelId + 1;
        }
        $attributes = array_merge([$this->primaryKey => $modelPrimaryKeyValue], $this->getAttributes());
        $this->setRawAttributes($attributes);
    }

    /**
     * Get all of the models from the json file.
     *
     * @return Collection
     */
    protected function getFromJson()
    {
        if (! $this->modelsFromJson) {
            $this->loadModelsFromJson();
        }

        return $this->modelsFromJson;
    }

    /**
     * Load all of the models from the json file in the "modelsFromJson" variable.
     *
     * @return void
     */
    protected function loadModelsFromJson()
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
     * Get an array containing all of the models from the json file.
     *
     * @return array
     */
    protected function getRawArrayFromJson()
    {
        $modelsArray = [];
        if (file_exists($this->getJsonStoragePath())) {
            $modelsArray = $this->fromJson(File::get($this->getJsonStoragePath()));
        }

        return $modelsArray;
    }

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param  string $value
     * @param  bool   $asObject
     *
     * @return mixed
     */
    public abstract function fromJson($value, $asObject = false);

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public abstract function getAttributes();

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array $attributes
     * @param  bool  $sync
     *
     * @return Model
     */
    public abstract function setRawAttributes(array $attributes, $sync = false);

    /**
     * Apply the "where" clauses on the collection.
     *
     * @param $modelsCollection
     *
     * @return void
     */
    protected function applyWhereClauses(Collection &$modelsCollection)
    {
        foreach ($this->wheres as $where) {
            $modelsCollection->where($where['column'], $where['operator'], $where['value']);
        }
    }

    /**
     * Apply the "whereIn" clauses on the collection.
     *
     * @param $modelsCollection
     *
     * @return void
     */
    protected function applyWhereInClauses(Collection &$modelsCollection)
    {
        foreach ($this->whereIns as $whereIn) {
            $modelsCollection->whereIn($whereIn['column'], $whereIn['values']);
        }
    }

    /**
     * Apply the "whereNotIn" clauses on the collection.
     *
     * @param $modelsCollection
     *
     * @return void
     */
    protected function applyWhereNotInClauses(Collection &$modelsCollection)
    {
        foreach ($this->whereNotIns as $whereNotIn) {
            $modelsCollection->whereNotIn($whereNotIn['column'], $whereNotIn['values']);
        }
    }

    /**
     * Apply the "orderBy" clauses on the collection.
     *
     * @param $modelsCollection
     *
     * @return void
     */
    protected function applyOrderByClauses(Collection &$modelsCollection)
    {
        foreach ($this->orderBys as $orders) {
            $modelsCollection = $modelsCollection->sortBy(
                $orders['column'],
                SORT_REGULAR,
                $orders['direction'] === 'desc'
            );
        }
    }

    /**
     * Apply the "select" clauses on the collection.
     *
     * @param $modelsCollection
     *
     * @return void
     */
    protected function applySelectClauses(Collection &$modelsCollection)
    {
        $modelsCollection->only(array_unique($this->selects));
    }

    /**
     * Delete the model from the json file.
     *
     * @return void
     */
    protected function deleteModelFromJson()
    {
        $withoutCurrentModel = $this->whereNotIn($this->primaryKey, [$this->getAttribute($this->primaryKey)])->get();
        File::put($this->getJsonStoragePath(), $withoutCurrentModel->toJson());
    }
}
