<?php

namespace Weeks\Laravel\Repositories\Traits;

/**
 * @todo Clear cache if a record is updated. Maybe use updated_at as cache key.
 */
trait CacheResults
{
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
     * @param $callback
     * @param $method
     * @param $args
     * @return mixed
     */
    protected function processCacheRequest($callback, $method, $args)
    {
        if ($this->isCaching() && !in_array($method, $this->getIgnoredMethods())) {

            $key = $this->createCacheKey($method, $args);

            return $this->getCache()->remember($key, $this->getCacheTtl(), $callback);
        }

        return $callback();
    }

    /**
     * Make a unique key for this specific request.
     *
     * @param $functionName string Name of method to call.
     * @param $args array Argument to pass into the method.
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