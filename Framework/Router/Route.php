<?php


namespace Framework\Router;

use Framework\Base as Base;
//use Framework\Router\Exception as Exception;
use Framework\Core\Exception as Exception;

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

    public function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }
}