<?php

namespace Framework\Configuration\Driver;

use Framework\ArrayMethods;
use Framework\Configuration;
//use Framework\Configuration\Exception;
use Framework\Core\Exception;

class AssociativeArray extends Configuration\Driver
{
    public function parse($path)
    {
        if (empty($path))
        {
            throw new Exception\Argument("Array is empty or not valid.");
        }

        if (!isset($this->_parsed[$path]))
        {
            include_once ("{$path}.php");
            $configurationArray = $config;
            $this->_parsed[$path] = ArrayMethods::toObject($configurationArray);
        }

        return $this->_parsed[$path];
    }
}