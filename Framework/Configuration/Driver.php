<?php

namespace Framework\Configuration;

use Framework\Base;
//use Framework\Configuration\Exception;
use Framework\Core\Exception;

class Driver extends Base
{
    protected $_parsed = array();

    public function initialise()
    {
        return $this;
    }

    protected function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }
}
