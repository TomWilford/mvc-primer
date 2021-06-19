<?php
namespace Framework;

use Framework\Base;
use Framework\Registry;
use Framework\Events;
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
        Events::fire("framework.database.initialize.before", [$this->type, $this->options]);

        if (!$this->type) {
            /** @var false | Configuration $configuration */
            $configuration = Registry::get("configuration");

            if ($configuration) {
                $configuration = $configuration->initialise();
                $parsed        = $configuration->parse("../Application/Configuration/_database");

                if (!empty($parsed->database->default) && !empty($parsed->database->default->type)) {
                    $this->type    = $parsed->database->default->type;
                    unset($parsed->database->default->type);
                    $this->options = (array) $parsed->database->default;
                }
            }
        }

        Events::fire("framework.database.initialize.after", [$this->type, $this->options]);

        if (!$this->type) {
            throw new Exception\Argument("Invalid type");
        }

        if (!$this->options) {
            throw new Exception\Argument("No options provided");
        }

        switch ($this->type) {
            case "mysqli":
                return new Database\Connector\Mysqli($this->options);
            case "mysql_pdo":
                return new Database\Connector\MysqlPDO($this->options);
            default:
                throw new Exception\Argument("Invalid type");
        }
    }
}
