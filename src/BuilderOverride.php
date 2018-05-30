<?php

namespace Okipa\LaravelModelJsonStorage;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

trait BuilderOverride
{
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
     * Get the number of models to return per page.
     *
     * @return int
     */
    abstract public function getPerPage();

    /**
     * Load all of the models from the json file in the "modelsFromJson" variable.
     *
     * @return Collection
     */
    abstract protected function loadModelsFromJson();

    /**
     * Execute the query and get the first result.
     *
     * @param  array $columns
     *
     * @return Model|null
     */
    abstract public function first(array $columns = ['*']);

    /**
     * Set the column to be selected.
     *
     * @param  string $column
     *
     * @return $this
     */
    public function select(string $column)
    {
        $this->selects[] = $column;

        return $this;
    }

    /**
     * Add a new select column to the query.
     *
     * @param  string $column
     *
     * @return $this
     */
    public function addSelect(string $column)
    {
        $this->selects[] = $column;

        return $this;
    }

    /**
     * Add a "where in" clause to the query.
     *
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
     * Add a "where not in" clause to the query.
     *
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
     * Add an "order by" clause to the query.
     *
     * @param  string $column
     * @param  string $direction
     *
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'asc')
    {
        $this->orderBys[] = compact('column', 'direction');

        return $this;
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param  int   $id
     * @param  array $columns
     *
     * @return mixed|static
     */
    public function find(int $id, array $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed $id
     * @param  array $columns
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);
        if (is_array($id)) {
            if (count($result) == count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }
        throw (new ModelNotFoundException)->setModel(
            get_class($this), $id
        );
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string $column
     * @param  mixed  $operator
     * @param  mixed  $value
     *
     * @return $this
     */
    public function where(string $column, $operator = null, $value = null)
    {
        if (! isset($value)) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = compact('column', 'operator', 'value');

        return $this;
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param string $column
     * The "pluck" collection method is used under the hood.
     *
     * @return Collection
     */
    public function value(string $column)
    {
        return $this->get()->pluck($column)->first();
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $columns
     *
     * @return Collection
     */
    public function get(array $columns = ['*'])
    {
        if ($columns !== ['*']) {
            $this->selects = $columns;
        }
        $modelsCollection = $this->loadModelsFromJson();
        $this->applyWhereClauses($modelsCollection);
        $this->applyWhereInClauses($modelsCollection);
        $this->applyWhereNotInClauses($modelsCollection);
        $this->applyOrderByClauses($modelsCollection);
        $this->applySelectClauses($modelsCollection);

        return $modelsCollection->values();
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param string $column
     * @param string null|$key
     *
     * @return Collection
     */
    public function pluck(string $column, string $key = null)
    {
        return $this->get()->pluck($column, $key);
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  array $columns
     *
     * @return int
     */
    public function count(array $columns = ['*'])
    {
        return $this->get($columns)->count();
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param $column
     *
     * @return int
     */
    public function min(string $column)
    {
        return $this->get()->min($column);
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param  string $column
     *
     * @return int
     */
    public function max(string $column)
    {
        return $this->get()->max($column);
    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param  string $column
     *
     * @return mixed
     */
    public function avg($column)
    {
        return $this->get()->avg($column);
    }

    /**
     * Paginate the given query.
     *
     * @param  int|null $perPage
     * @param  array    $columns
     * @param  string   $pageName
     * @param  int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \InvalidArgumentException
     */
    public function paginate(int $perPage = null, array $columns = ['*'], string $pageName = 'page', int $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->getPerPage();
        $modelsCollection = $this->get($columns);
        $items = $modelsCollection->forPage($page, $perPage)->values();
        $total = $modelsCollection->count();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path'     => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }

    /**
     * Apply the "where" clauses on the collection.
     *
     * @param $modelsCollection
     *
     * @return void
     */
    protected function applyWhereClauses(Collection &$modelsCollection)
    {
        if (! empty($this->wheres) && ! $modelsCollection->isEmpty()) {
            foreach ($this->wheres as $where) {
                $modelsCollection = $modelsCollection->where($where['column'], $where['operator'], $where['value']);
            }
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
        if (! empty($this->whereIns) && ! $modelsCollection->isEmpty()) {
            foreach ($this->whereIns as $whereIn) {
                $modelsCollection = $modelsCollection->whereIn($whereIn['column'], $whereIn['values']);
            }
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
        if (! empty($this->whereNotIns) && ! $modelsCollection->isEmpty()) {
            foreach ($this->whereNotIns as $whereNotIn) {
                $modelsCollection = $modelsCollection->whereNotIn($whereNotIn['column'], $whereNotIn['values']);
            }
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
        if (! empty($this->orderBys) && ! $modelsCollection->isEmpty()) {
            foreach ($this->orderBys as $orders) {
                $modelsCollection = $modelsCollection->sortBy(
                    $orders['column'],
                    SORT_REGULAR,
                    $orders['direction'] === 'desc'
                );
            }
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
        if (! empty($this->selects) && $this->selects !== ['*'] && ! $modelsCollection->isEmpty()) {
            $selectCollection = new Collection();
            $modelsCollection->each(function($model) use ($selectCollection) {
                $selectCollection->push(collect($model->toArray())->only(array_unique($this->selects)));
            });
            $modelsCollection = $selectCollection;
        }
    }
}
