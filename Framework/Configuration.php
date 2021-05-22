<?php

namespace Framework;

use Framework\Base;
use Framework\Events;
use Framework\Configuration\Exception;

class Configuration extends Base
{
    /**
     * @var string $_type Configuration file type
     * @readwrite
     */
    protected $_type;

    /**
     * @var array $_options Additional configuration options
     * @readwrite
     */
    protected $_options;

    public function initialise()
    {
        Events::fire("framework.configuration.initialize.before", [$this->type, $this->options]);

        if (!$this->_type)
        {
            throw new Exception\Argument("Invalid type");
        }

        Events::fire("framework.configuration.initialize.after", [$this->type, $this->options]);

        switch ($this->_type)
        {
            case "ini":
                return new Configuration\Driver\Ini($this->_options);
                break;
            case "json":
                return new Configuration\Driver\Json($this->_options);
                break;
            case "array":
                return new Configuration\Driver\AssociativeArray($this->_options);
                break;
            default:
                throw new Exception\Argument("Invalid Type");
        }
    }

}
