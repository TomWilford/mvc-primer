<?php


namespace Framework;

use Framework\Base as Base;
//use Framework\Database as Database;
//use Framework\Database\Exception as Exception;
use Framework\Core\Exception as Exception;

class Database extends Base
{
    /**
     * @var
     * @readwrite
     */
    protected $_type;

    /**
     * @var
     * @readwrite
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
            case "mysql":
                return new Database\Connector\Mysqli($this->options);
                break;
            default:
                throw new Exception\Argument("Invalid type");
        }
    }

}