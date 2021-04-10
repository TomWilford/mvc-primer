<?php

namespace Framework;

use Framework\Base;
use Framework\Configuration\Driver\Ini;
use Framework\Database\Exception;

class Database extends Base
{
    /**
     * @var string $_type type of database driver to use
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

    /**
     * @throws Configuration\Exception\Argument
     * @throws Exception\Argument
     */
    public function initialise()
    {
        if (!$this->type)
        {
            /** @var false | Configuration $configuration */
            $configuration = Registry::get("configuration");

            if ($configuration)
            {
                $configuration = $configuration->initialise();
                $parsed        = $configuration->parse("Configuration/_database");

                if (!empty($parsed->database->default) && !empty($parsed->database->default->type))
                {
                    $this->type    = $parsed->database->default->type;
                    unset($parsed->database->default->type);
                    $this->options = (array) $parsed->database->default;
                }
            }
        }

        if (!$this->type)
        {
            throw new Exception\Argument("Invalid type");
            //$this->type = "mysql_pdo";
        }

        if (!$this->options)
        {
            throw new Exception\Argument("No options provided");
            /*$this->options = [
                "host"     => "localhost",
                "username" => "prophpmvc",
                "password" => "prophpmvc",
                "schema"   => "prophpmvc",
                "port"     => "3306"
            ];*/
        }

        switch ($this->type)
        {
            case "mysqli":
                return new Database\Connector\Mysqli($this->options);
                break;
            case "mysql_pdo":
                return new Database\Connector\MysqlPDO($this->options);
                break;
            default:
                throw new Exception\Argument("Invalid type");
        }
    }

}