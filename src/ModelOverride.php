<?php

namespace Okipa\LaravelModelJsonStorage;

use Exception;

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
}
