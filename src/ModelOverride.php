<?php

namespace Okipa\LaravelModelJsonStorage;

use Exception;
use File;
use Illuminate\Support\Collection;

trait ModelOverride
{
    /**
     * Get all of the models from the json file.
     *
     * @param array $columns
     *
     * @return Collection
     */
    public static function all($columns = ['*'])
    {
        return (new static)->get($columns);
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $column
     * @param array  $values
     *
     * @return $this
     */
    abstract public function whereNotIn(string $column, array $values);
    
    

    /**
     * Get the class name of the parent model.
     *
     * @return string
     */
    abstract public function getMorphClass();

    /**
     * Load all of the models from the json file in the "modelsFromJson" variable.
     *
     * @return Collection
     */
    abstract protected function loadModelsFromJson();

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $columns
     *
     * @return Collection
     */
    abstract public function get(array $columns = ['*']);

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array $attributes
     *
     * @return $this
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    abstract public function fill(array $attributes);

    /**
     * Determine if the model uses timestamps.
     *
     * @return bool
     */
    abstract public function usesTimestamps();

    /**
     * Get a fresh timestamp for the model.
     *
     * @return string
     */
    abstract public function freshTimestampString();

    /**
     * Set the value of the "created at" attribute.
     *
     * @param  mixed $value
     *
     * @return $this
     */
    abstract public function setCreatedAt($value);

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed $value
     *
     * @return $this
     */
    abstract public function setUpdatedAt($value);

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    abstract public function getAttribute($key);

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    abstract public function getAttributes();

    /**
     * Make the given, typically hidden, attributes visible.
     *
     * @param  array|string $attributes
     *
     * @return $this
     */
    abstract public function makeVisible($attributes);

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array $attributes
     * @param  bool  $sync
     *
     * @return $this
     */
    abstract public function setRawAttributes(array $attributes, $sync = false);

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    abstract public function getHidden();

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    abstract public function getKeyName();

    /**
     * Save the model to the json file.
     *
     * @param  array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        return $this->saveToJson();
    }

    /**
     * Save the model to the json file.
     *
     * @return bool
     */
    protected function saveToJson()
    {
        if ($this->{$this->getKeyName()}) {
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
        $withoutCurrentModel = $this->whereNotIn(
            $this->getKeyName(),
            [$this->getAttribute($this->getKeyName())]
        )->get();
        $models = $withoutCurrentModel->push($this->makeVisible($this->getHidden()))->sortBy($this->getKeyName());
        File::put($this->getJsonStoragePath(), $models->toJson());
    }

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
        $this->setUpdatedAt($now);
        if (! $update) {
            $this->setCreatedAt($now);
        }
    }

    /**
     * Get the storage path for the model json file.
     *
     * @return string
     */
    public function getJsonStoragePath()
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
     * Save a new model in the json file.
     *
     * @return void
     */
    protected function createModelInJson()
    {
        if ($this->usesTimestamps()) {
            $this->setTimestampFields();
        }
        $this->setModelPrimaryKeyValue();
        $models = $this->all()->push($this->makeVisible($this->getHidden()));
        File::put($this->getJsonStoragePath(), $models->toJson());
    }

    /**
     * Set the model primary key by incrementing from the bigger id found in the json file.
     *
     * @return void
     */
    protected function setModelPrimaryKeyValue()
    {
        $modelPrimaryKeyValue = 1;
        $modelsCollection = $this->loadModelsFromJson();
        if (! $modelsCollection->isEmpty()) {
            $lastModelId = $modelsCollection->sortBy('id')->last()->getAttribute($this->getKeyName());
            $modelPrimaryKeyValue = $lastModelId + 1;
        }
        $this->setRawAttributes(array_merge(['id' => $modelPrimaryKeyValue], $this->getAttributes()));
    }

    /**
     * Update the model in the json file.
     *
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
     * Delete the model from the json file.
     *
     * @return bool|null
     * @throws Exception
     */
    public function delete()
    {
        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }
        $this->deleteModelFromJson();

        return true;
    }

    /**
     * Delete the model from the json file.
     *
     * @return void
     */
    protected function deleteModelFromJson()
    {
        $withoutCurrentModel = $this->whereNotIn(
            $this->getKeyName(),
            [$this->getAttribute($this->getKeyName())]
        )->get();
        File::put($this->getJsonStoragePath(), $withoutCurrentModel->values()->toJson());
    }
}
