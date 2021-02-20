<?php

namespace Framework\Cache;

use Framework\Base;
use Framework\Cache\Exception;

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