<?php

namespace Weeks\Laravel\Repositories;

trait CacheResults
{
    // Implement Parent Methods

    public function getAll($columns = null, $orderBy = 'created_at', $sort = 'DECS')
    {
        return $this->processRequest(__FUNCTION__, func_get_args());
    }

    public function getPaginated($paged = 15, $orderBy = 'created_at', $sort = 'DECS')
    {
        return $this->processRequest(__FUNCTION__, func_get_args());
    }

    public function getForSelect($data, $key = 'id', $orderBy = 'created_at', $sort = 'DECS')
    {
        return $this->processRequest(__FUNCTION__, func_get_args());
    }

    public function getById($id)
    {
        return $this->processRequest(__FUNCTION__, func_get_args());
    }

    public function getItemByColumn($term, $column = 'slug')
    {
        return $this->processRequest(__FUNCTION__, func_get_args());
    }

    public function getCollectionByColumn($term, $column = 'slug')
    {
        return $this->processRequest(__FUNCTION__, func_get_args());
    }

    public function getActively($term, $column = 'slug')
    {
        return $this->processRequest(__FUNCTION__, func_get_args());
    }


    /**
     * @return bool
     */
    public function isCaching()
    {
        return true;
    }

    /**
     * Get ttl (minutes).
     *
     * @return int
     */
    public function getCacheTtl()
    {
        return isset($this->cacheTtl) ? $this->cacheTtl : 60;
    }

    /**
     * Methods that should never be cached.
     *
     * @return array
     */
    public function getIgnoredMethods()
    {
        return isset($this->ignoredMethods) ? $this->ignoredMethods : [];
    }

    /**
     * Perform the query and cache if required.
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    protected function processRequest($method, $args)
    {
        if ($this->isCaching() && ! in_array($method, $this->getIgnoredMethods())) {

            $key = $this->createCacheKey($method, $args);

            return $this->getCache()->remember($key, $this->getCacheTtl(), function () use ($method, $args) {
                return call_user_func_array(['parent', $method], $args);
            });
        }

        return call_user_func_array(['parent', $method], $args);
    }

    /**
     * Make a unique key for this specific request.
     *
     * @param $functionName Name of method to call.
     * @param $args Argument to pass into the method.
     * @return string
     */
    public function createCacheKey($functionName, $args)
    {
        return sprintf('%s.%s.%s', get_class(), $functionName, md5(implode('|', $args)));
    }

    /**
     * returns Illuminate\Contracts\Cache\Repository
     */
    protected function getCache()
    {
        return app()->make('Illuminate\Contracts\Cache\Repository');
    }
}