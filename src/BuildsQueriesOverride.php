<?php

namespace Okipa\LaravelModelJsonStorage;

use Illuminate\Support\Collection;

trait BuildsQueriesOverride
{
    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $columns
     *
     * @return Collection
     */
    public abstract function get(array $columns = ['*']);

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
}
