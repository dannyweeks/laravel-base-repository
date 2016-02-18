<?php

namespace Weeks\Laravel\Repositories\Traits;

use Symfony\Component\HttpKernel\Exception\HttpException;

trait ThrowsHttpExceptions
{
    /**
     * @param $column
     * @param $id
     * @throws HttpException
     */
    protected function throwItemNotFoundHttpException($column, $id)
    {
        $message = sprintf(
            'Item not found with the %s of \'%s\' in the %s model via the %s.',
            $column,
            $id,
            class_basename($this->model),
            class_basename($this)
        );
        throw new HttpException(404, $message);
    }
}