<?php

namespace Weeks\Laravel\Repositories\Traits;

use Symfony\Component\HttpKernel\Exception\HttpException;

trait ThrowsHttpExceptions
{
    /**
     * @param string $methodName
     * @param string $args
     */
    protected function throwNotFoundHttpException($methodName = '', $args = '')
    {
        $columnIdFormat = 'Item not found with the %3$s of \'%4$s\' in the %1$s model via the %2$s.';
        $format = 'Requested item does not exist in the %s model via %s.';
        $data = [];

        if ($methodName == 'getById') {
            $format = $columnIdFormat;
            $data = ['id', $args[0]];
        }

        if ($methodName == 'getItemByColumn') {
            $format = $columnIdFormat;
            $data = [$args[1], $args[0]];
        }

        $message = $this->createExceptionMessage($format, $data);

        throw new HttpException(404, $message);
    }

    private function createExceptionMessage($format, $data = [])
    {
        return vsprintf(
            $format,
            array_merge([
                class_basename($this->model),
                class_basename($this)
            ], $data)
        );
    }
}