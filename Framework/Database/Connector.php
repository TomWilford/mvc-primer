<?php

namespace Framework\Database;

use Framework\Base;
//use Framework\Database\Exception;
use Framework\Core\Exception;

class Connector extends Base
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