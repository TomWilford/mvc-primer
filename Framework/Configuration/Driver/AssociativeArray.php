<?php


namespace Framework\Configuration\Driver;

use Framework\ArrayMethods as ArrayMethods;
use Framework\Configuration as Configuration;
//use Framework\Configuration\Exception as Exception;
use Framework\Core\Exception as Exception;

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
    }

}