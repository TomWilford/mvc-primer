<?php


namespace Framework\Configuration\Driver;

use Framework\ArrayMethods as ArrayMethods;
use Framework\Configuration as Configuration;
//use Framework\Configuration\Exception as Exception;
use Framework\Core\Exception as Exception;


class Json extends Configuration\Driver
{

    public function parse($path)
    {
        if (empty($path))
        {
            throw new Exception\Argument("\$path argument is not valid");

        }

        if (!isset($this->_parsed[$path]))
        {
            $config = array();

            $string = file_get_contents("{$path}.json");

            $pairs = json_decode($string, true);

            if ($pairs == false)
            {
                throw new Exception\Syntax("Could not parse json configuration file.");
            }

            $this->_parsed[$path] = ArrayMethods::toObject($config);

            return $this->_parsed[$path];
        }
    }
}