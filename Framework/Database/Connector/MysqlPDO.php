<?php

namespace Framework\Database\Connector;

use Framework\Database;
use Framework\Database\Exception;
use PDO;
use PDOException;
use PDOStatement;

class MysqlPDO extends Database\Connector
{
    protected  $_service;

    /**
     * @var
     * @readwrite
     */
    protected $_host;

    /**
     * @var
     * @readwrite
     */
    protected $_username;

    /**
     * @var
     * @readwrite
     */
    protected $_password;

    /**
     * @var
     * @readwrite
     */
    protected $_schema;

    /**
     * @var
     * @readwrite
     */
    protected $_port;

    /**
     * @var string
     * @readwrite
     */
    protected $_charset = "utf8mb4";

    /**
     * @var
     * @readwrite
     */
    protected $_dsn;

    /**
     * @var string
     * @readwrite
     */
    protected $_engine = "InnoDB";

    /**
     * @var array
     * @readwrite
     */
    protected $_options = [];

    /**
     * @var bool
     * @readwrite
     */
    protected $_isConnected = false;

    protected function _isValidService()
    {
        $isEmpty = empty($this->_service);
        $isInstance = $this->_service instanceof \PDO;

        if ($this->_isConnected && $isInstance && !$isEmpty)
        {
            return true;
        }

        return false;
    }

    public function connect()
    {
        if (!$this->_isValidService())
        {
            $this->_dsn = "mysql:host={$this->_host};dbname={$this->_schema};port={$this->_port};charset={$this->_charset}";
            $this->_options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ];
            try
            {
                $this->_service = new \PDO(
                    $this->_dsn, $this->_username, $this->_password, $this->_options
                );
            }
            catch (\PDOException $e)
            {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }

            $this->_isConnected = true;
        }

        return true;
    }

    public function disconnect()
    {
        if ($this->_isValidService())
        {
            $this->_isConnected = false;
            $this->_service->close();
        }

        return $this;
    }

    public function query()
    {
        return new Database\Query\MysqlPDO([
            "connector" => $this
        ]);
    }

    public function execute($sql)
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->query($sql);
    }

    public function getLastInsertId()
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->lastInsertId();
    }

    /**
     * @param $result PDOStatement
     * @return mixed
     * @throws Exception\Service
     */
    public function getAffectedRows($result)
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $result->rowCount();
    }

    /**
     * @param $result PDOStatement
     * @return mixed
     * @throws Exception\Service
     */
    public function getLastErrorMessage($result)
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $result->errorInfo();
    }

    /**
     * @param $result PDOStatement
     * @return mixed
     * @throws Exception\Service
     */
    public function getLastErrorCode($result)
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $result->errorCode();
    }

    public function sync($model)
    {
        $lines    = [];
        $indices  = [];
        $columns  = $model->columns;
        $template = "CREATE TABLE `%s` (\n%s, \n%s\n) ENGINE=%s DEFAULT CHARSET=%s;";

        foreach ($columns as $column)
        {
            $raw    = $column["raw"];
            $name   = $column["name"];
            $type   = $column["type"];
            $length = $column["length"];

            if ($column["primary"])
            {
                $indices[] = "PRIMARY KEY (`{$name}`)";
            }
            if ($column["index"])
            {
                $indices[] = "KEY `{$name}` (`{$name}`)";
            }

            switch ($type)
            {
                case "autonumber":
                    $lines[] = "`{$name}` int(11) NOT NULL AUTO_INCREMENT";
                    break;
                case "text":
                    if ($length !== null && $length <= 255)
                    {
                        $lines[] = "`{$name} varchar({$length}) DEFAULT NULL";
                    }
                    else
                    {
                        $lines[] = "`{$name}` text";
                    }
                    break;
                case "integer":
                    $lines[] = "`{$name}` int(11) DEFAULT NULL";
                    break;
                case "decimal":
                    $lines[] = "`{$name}` float DEFAULT NULL";
                    break;
                case "boolean":
                    $lines[] = "`{$name}` tinyint(4) DEFAULT NULL";
                    break;
                case "datetime":
                    $lines[] = "`{$name}` datetime DEFAULT NULL";
            }

            $table = $model->table;
            $sql = sprintf(
                $template,
                $table,
                join(",\n", $lines),
                join(",\n", $indices),
                $this->_engine,
                $this->_charset
            );

            $result = $this->execute("DROP TABLE IF EXISTS {$table};");
            if ($this->getLastErrorCode($result))
            {
                $error = $this->getLastErrorMessage($result);

            }
        }
    }
}