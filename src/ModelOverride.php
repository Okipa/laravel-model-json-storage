<?php

namespace Okipa\LaravelModelJsonStorage;

use Exception;
use Illuminate\Support\Collection;

trait ModelOverride
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public abstract function fill(array $attributes);

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $columns
     *
     * @return Collection
     */
    public abstract function get(array $columns = ['*']);

    /**
     * Determine if the model uses timestamps.
     *
     * @return bool
     */
    public abstract function usesTimestamps();

    /**
     * Get a fresh timestamp for the model.
     *
     * @return string
     */
    public abstract function freshTimestampString();

    /**
     * Set the value of the "created at" attribute.
     *
     * @param  mixed  $value
     * @return $this
     */
    public abstract function setCreatedAt($value);

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed  $value
     * @return $this
     */
    public abstract function setUpdatedAt($value);

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public abstract function getAttribute($key);

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public abstract function getAttributes();

    /**
     * Make the given, typically hidden, attributes visible.
     *
     * @param  array|string  $attributes
     * @return $this
     */
    public abstract function makeVisible($attributes);

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array  $attributes
     * @param  bool  $sync
     * @return $this
     */
    public abstract function setRawAttributes(array $attributes, $sync = false);

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public abstract function getHidden();

    /**
     * Get all of the models from the json file.
     *
     * @return Collection
     */
    protected abstract function getFromJson();

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public abstract function getKeyName();

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
     * Delete the model from the json file.
     *
     * @return void
     */
    protected function deleteModelFromJson()
    {
        $withoutCurrentModel = $this->whereNotIn($this->primaryKey, [$this->getAttribute($this->primaryKey)])->get();
        File::put($this->getJsonStoragePath(), $withoutCurrentModel->toJson());
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
}
