<?php

namespace Weeks\Laravel\Repositories\Traits;

use Symfony\Component\HttpKernel\Exception\HttpException;

trait ThrowsHttpExceptions
{
    private $baseThrowableMethods = ['getById', 'getItemByColumn'];

    protected $exceptionsDisabled = false;

    /**
     * @return $this
     */
    public function disableHttpExceptions()
    {
        $this->exceptionsDisabled = true;

        return $this;
    }

    /**
     * @param $result
     * @param $methodName
     * @return bool
     */
    protected function shouldThrowHttpException($result, $methodName)
    {
        return $this->exceptionsDisabled === false
        && in_array($methodName, $this->getThrowableMethods())
        && is_null($result);
    }

    /**
     * @return array
     */
    protected function getThrowableMethods()
    {
        if (isset($this->throwableMethods)) {
            return array_merge($this->baseThrowableMethods, $this->throwableMethods);
        }

        return $this->baseThrowableMethods;
    }

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