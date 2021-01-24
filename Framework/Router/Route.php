<?php

namespace Framework\Router;

use Framework\Base;
//use Framework\Router\Exception;
use Framework\Core\Exception;

class Route extends Base
{
    /**
     * @var
     * @readwrite
     */
    protected $_pattern;

    /**
     * @var
     * @readwrite
     */
    protected $_controller;

    /**
     * @var
     * @readwrite
     */
    protected $_action;

    /**
     * @var array
     * @readwrite
     */
    protected $_parameters = array();

    /**
     * @var string[]
     */
    protected $_aliases = [
        #"example/url/call" => "path/to/function"
    ];

    public function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }

    public function returnMatchingAlias($url)
    {
        if (in_array($url, array_keys($this->_aliases)))
        {
            return $this->_aliases[$url];
        }
        return $url;
    }
}