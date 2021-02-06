<?php

namespace Framework;

use Framework\Base;
//use Framework\Cache\Exception;
use Framework\Core\Exception;

class Cache extends Base
{
    /**
     * @var string $_type Caching engine to use
     * @readwrite true
     */
    protected $_type;

    /**
     * @var array $_options Settings for caching engine
     * @readwrite true
     */
    protected $_options;

    protected function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }

    public function initialise()
    {
        if (!$this->type)
        {
            throw new Exception\Argument("Invalid type");
        }

        switch ($this->type)
        {
            case "memcached":
                return new Cache\Driver\Memcached($this->options);
                break;
            default:
                throw new Exception\Argument("Invalid type");
                break;
        }
    }
}