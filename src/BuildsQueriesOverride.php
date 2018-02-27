<?php

namespace Okipa\LaravelModelJsonStorage;

trait BuildsQueriesOverride
{
    /**
     * Execute the query and get the first result.
     *
     * @param  array $columns
     *
     * @return Model|null
     */
    public function first(array $columns = ['*'])
    {
        return $this->get($columns)->first();
    }

    /**
     * Chunk the results of the query.
     *
     * @param  int $count
     *
     * @return Collection
     */
    public function chunk(int $count)
    {
        return $this->get()->chunk($count);
    }
}
