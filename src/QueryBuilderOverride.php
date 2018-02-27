<?php

namespace Okipa\LaravelModelJsonStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

trait QueryBuilderOverride
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
     * Set the column to be selected.
     *
     * @param  string $column
     *
     * @return Model
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
     * @return Model
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
     * Add a "group by" clause to the query.
     *
     * @param $column
     *
     * @return Collection
     */
    public function groupBy($column)
    {
        return $this->get()->unique($column);
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
        $modelsCollection = $this->getFromJson();
        $this->applyWhereClauses($modelsCollection);
        $this->applyWhereInClauses($modelsCollection);
        $this->applyWhereNotInClauses($modelsCollection);
        $this->applyOrderByClauses($modelsCollection);
        $this->applySelectClauses($modelsCollection);

        return $modelsCollection;
    }

    /**
     * Force the query to only return distinct results.
     *
     * @return Collection
     */
    public function distinct(string $column)
    {
        return $this->get()->unique($column);
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
     * Add a basic where clause to the query.
     *
     * @param  string $column
     * @param  string $operator
     * @param  null|mixed  $value
     *
     * @return Model
     */
    public function where(string $column, string $operator = null, mixed $value = null)
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
        return $this->get()->pluck($column);
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param string $column
     * @param string $key
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
     * @return int
     */
    public function avg(string $column)
    {
        return $this->get()->avg($column);
    }

    /**
     * Paginate the given query.
     *
     * @param  int      $perPage
     * @param  int|null $page
     * @param array     $options
     *
     * @return LengthAwarePaginator
     */
    public function paginate($perPage = null, $page = null, $options = [])
    {
        $modelsCollection = $this->get();
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        return new LengthAwarePaginator(
            $modelsCollection->forPage($page, $perPage),
            $modelsCollection->count(),
            $perPage,
            $page,
            $options
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
}
