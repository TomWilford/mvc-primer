<?php

namespace Framework\Cache;

use Framework\Base as Base;
use Framework\Core\Exception as Exception;

class Driver extends Base
{
    public function initialise()
    {
        return $this;
    }

    protected function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }
}