<?php namespace Acme\Repositories;

abstract class BaseRepository{

    /**
     * Name of model associated with this repository
     * @var string
     */
    protected $model;

    /**
     * Array of method names of which to include as relationships
     * @var array
     */
    protected $relationships = [];

    /**
     * Get the model from the IoC container
     */
    public function __construct()
    {
        $this->model = app()->make($this->model);
    }

    /**
     * Get all items
     * @param  string $columns specific columns to select
     * @param  string $orderBy column to sort by
     * @param  string $sort    sort direction
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAll($columns = null, $orderBy = 'created_at', $sort = 'DECS')
    {
        return $this->model
                    ->with($this->relationships)
                    ->orderBy($orderBy, $sort)
                    ->get($columns);
    }

    /**
     * Get paged items
     * @param  integer $paged   Items per page
     * @param  string  $orderBy Column to sort by
     * @param  string  $sort    Sort direction
     * @return Illuminate\Pagination\Paginator
     */
    public function getPaginated($paged = 15, $orderBy = 'created_at', $sort = 'DECS')
    {
        return $this->model
                    ->with($this->relationships)
                    ->orderBy($orderBy, $sort)
                    ->paginate($paged);
    }

    /**
     * Items for select options
     * @param  string $data    column to display in the option
     * @param  string $key     column to be used as the value in option
     * @param  string $orderBy column to sort by
     * @param  string $sort    sort direction
     * @return array           array with key value pairs
     */
    public function getForSelect($data, $key = 'id', $orderBy = 'created_at', $sort = 'DECS')
    {
        return $this->model
                    ->with($this->relationships)
                    ->orderBy($orderBy, $sort)
                    ->lists($data, $key);
    }

    /**
     * Get item by its id
     * @param  integer $id
     * @return Illuminate\Database\Eloquent\Model
     */
    public function getById($id)
    {
        return $this->model
                    ->with($this->relationships)
                    ->find($id);
    }

    /**
     * Get instance of model by column
     * @param  mixed $term    search term
     * @param  string $column column to search
     * @return Illuminate\Database\Eloquent\Model
     */
    public function getByColumn($term, $column = 'slug')
    {
        return $this->model
                    ->with($this->relationships)
                    ->where($column, '=', $term)
                    ->first();
    }

    /**
     * Get item by id or column
     * @param  mixed  $term   id or term
     * @param  string $column column to search
     * @return Illuminate\Database\Eloquent\Model
     */
    public function getActively($term, $column = 'slug')
    {
        if(is_numeric($term))
        {
            return $this->getById($term);
        }
        return $this->getByColumn($term, $column);
    }
}
