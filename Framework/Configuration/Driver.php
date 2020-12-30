<?php


namespace Framework\Configuration;

use Framework\Base as Base;
//use Framework\Configuration\Exception as Exception;
use Framework\Core\Exception as Exception;

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
