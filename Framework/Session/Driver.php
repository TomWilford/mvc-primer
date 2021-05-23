<?php

namespace Framework\Session;

use Framework\Base;
use Framework\Session\Exception;

class Driver extends Base
{
    public function initialise()
    {
        return $this;
    }

    protected function getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} not implemented");
    }
}
