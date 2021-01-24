<?php

namespace Framework;

use Framework\Base;
//use Framework\Database\Exception;
use Framework\Core\Exception;

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
    protected $_options = [];

    protected function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }

    public function initialise()
    {
        if (!$this->type)
        {
            //throw new Exception\Argument("Invalid type");
            $this->type = "pdo";
        }

        if (!$this->options)
        {
            //TODO - Get from config file
            $this->options = [
                "host"     => "localhost",
                "username" => "root",
                "password" => "",
                "schema"   => "mvc_primer",
                "port"     => "3306"
            ];
        }

        switch ($this->type)
        {
            case "mysqli":
                return new Database\Connector\Mysqli($this->options);
                break;
            case "pdo":
                return new Database\Connector\Mysql($this->options);
                break;
            default:
                throw new Exception\Argument("Invalid type");
        }
    }

}