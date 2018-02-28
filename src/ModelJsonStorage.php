<?php

namespace Okipa\LaravelModelJsonStorage;

use File;
use Illuminate\Support\Collection;

trait ModelJsonStorage
{
    use QueryBuilderOverride;
    use BuildsQueriesOverride;
    use ModelOverride;
    /**
     * The collection of models extracted from the json file.
     *
     * @var Collection
     */
    protected $modelsFromJson;

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
    abstract public function getHidden();

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param  string $value
     * @param  bool   $asObject
     *
     * @return mixed
     */
    abstract public function fromJson($value, $asObject = false);

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    abstract public function getMorphClass();

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
}
