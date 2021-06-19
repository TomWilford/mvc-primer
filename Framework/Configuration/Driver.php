<?php
namespace Framework\Configuration;

use Framework\Base;
use Framework\Configuration\Exception;

class Driver extends Base
{
    protected $_parsed = [];

    public function initialise()
    {
        return $this;
    }

    protected function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }
}
