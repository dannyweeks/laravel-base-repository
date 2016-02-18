<?php

namespace Weeks\Laravel\Repositories;

use Illuminate\Database\Eloquent\Model;
use Weeks\Laravel\Repositories\Traits\CacheResults;
use Weeks\Laravel\Repositories\Traits\ThrowsHttpExceptions;

abstract class BaseEloquentRepository implements RepositoryContract
{
    /**
     * Name of model associated with this repository
     * @var Model
     */
    protected $model;

    /**
     * Array of method names of relationships available to use
     * @var array
     */
    protected $relationships = [];

    /**
     * Array of relationships to include in next query
     * @var array
     */
    protected $requiredRelationships = [];

    /**
     * Array of traits being used by the repository.
     * @var array
     */
    protected $uses = [];

    /**
     * Get the model from the IoC container
     */
    public function __construct()
    {
        $this->model = app()->make($this->model);
        $this->setUses();
    }

    /**
     * Get all items
     *
     * @param  string $columns specific columns to select
     * @param  string $orderBy column to sort by
     * @param  string $sort sort direction
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll($columns = null, $orderBy = 'created_at', $sort = 'DECS')
    {
        $result = $this->model
            ->with($this->requiredRelationships)
            ->orderBy($orderBy, $sort)
            ->get($columns);

        return $this->applyTraits($result, __FUNCTION__, func_get_args());
    }

    /**
     * Get paged items
     *
     * @param  integer $paged Items per page
     * @param  string $orderBy Column to sort by
     * @param  string $sort Sort direction
     * @return \Illuminate\Pagination\Paginator
     */
    public function getPaginated($paged = 15, $orderBy = 'created_at', $sort = 'DECS')
    {
        $result = $this->model
            ->with($this->requiredRelationships)
            ->orderBy($orderBy, $sort)
            ->paginate($paged);

        return $this->applyTraits($result, __FUNCTION__, func_get_args());
    }

    /**
     * Items for select options
     *
     * @param  string $data column to display in the option
     * @param  string $key column to be used as the value in option
     * @param  string $orderBy column to sort by
     * @param  string $sort sort direction
     * @return array           array with key value pairs
     */
    public function getForSelect($data, $key = 'id', $orderBy = 'created_at', $sort = 'DECS')
    {
        $result = $this->model
            ->with($this->requiredRelationships)
            ->orderBy($orderBy, $sort)
            ->lists($data, $key)
            ->all();

        return $this->applyTraits($result, __FUNCTION__, func_get_args());
    }

    /**
     * Get item by its id
     *
     * @param  integer $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getById($id)
    {
        $result = $this->model
            ->with($this->requiredRelationships)
            ->find($id);

        return $this->applyTraits($result, __FUNCTION__, func_get_args());
    }

    /**
     * Get instance of model by column
     *
     * @param  mixed $term search term
     * @param  string $column column to search
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getItemByColumn($term, $column = 'slug')
    {
        $result = $this->model
            ->with($this->requiredRelationships)
            ->where($column, '=', $term)
            ->first();

        return $this->applyTraits($result, __FUNCTION__, func_get_args());
    }

    /**
     * Get instance of model by column
     *
     * @param  mixed $term search term
     * @param  string $column column to search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCollectionByColumn($term, $column = 'slug')
    {
        $result = $this->model
            ->with($this->requiredRelationships)
            ->where($column, '=', $term)
            ->get();

        return $this->applyTraits($result, __FUNCTION__, func_get_args());
    }

    /**
     * Get item by id or column
     *
     * @param  mixed $term id or term
     * @param  string $column column to search
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getActively($term, $column = 'slug')
    {
        if (is_numeric($term)) {
            return $this->getById($term);
        }

        return $this->getItemByColumn($term, $column);
    }

    /**
     * Create new using mass assignment
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a record using the primary key.
     *
     * @param $id mixed primary key
     * @param $data array
     */
    public function update($id, array $data)
    {
        return $this->model->where($this->model->getKeyName(), $id)->update($data);
    }

    /**
     * Update or crate a record and return the entity
     *
     * @param array $identifiers columns to search for
     * @param array $data
     * @return mixed
     */
    public function updateOrCreate(array $identifiers, array $data)
    {
        $existing = $this->model->where(array_only($data, $identifiers))->first();

        if ($existing) {
            $existing->update($data);

            return $existing;
        }

        return $this->create($data);
    }

    /**
     * Delete a record by the primary key.
     *
     * @param $id
     */
    public function delete($id)
    {
        return $this->model->where($this->model->getKeyName(), $id)->delete();
    }

    /**
     * Choose what relationships to return with query.
     *
     * @param null $relationships
     * @return $this
     */
    public function with($relationships = null)
    {
        $this->requiredRelationships = [];

        if ($relationships == 'all') {
            $this->requiredRelationships = $this->relationships;
        } elseif (is_array($relationships)) {
            $this->requiredRelationships = array_filter($relationships, function ($value) {
                return in_array($value, $this->relationships);
            });
        } elseif (is_string($relationships)) {
            array_push($this->requiredRelationships, $relationships);
        }

        return $this;
    }

    /**
     * Apply any trait functionality the repo is using.
     *
     * @param $result
     * @param $methodName
     * @param $arguments
     * @return mixed
     */
    protected function applyTraits($result, $methodName, $arguments)
    {
        $traits = $this->getUsedTraits();

        if (in_array(ThrowsHttpExceptions::class, $traits)) {
            if (is_null($result)) {
                $this->throwNotFoundHttpException($methodName, $arguments);
            }
        }

        if (in_array(CacheResults::class, $traits)) {
            
        }

        return $result;
    }

    /**
     *  The repository does not cache by default.
     * @return bool
     */
    public function isCaching()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getCacheTtl()
    {
        return 60;
    }

    /**
     * @return $this
     */
    protected function setUses()
    {
        $this->uses = array_flip(class_uses_recursive(get_class($this)));

        return $this;
    }

    /**
     * @return array
     */
    protected function getUsedTraits()
    {
        return $this->uses;
    }
}