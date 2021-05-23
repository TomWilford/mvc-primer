<?php

namespace Framework\Database\Connector;

use Framework\Database;
use Framework\Database\Exception;

/**
 * @deprecated
 */
class Mysqli extends Database\Connector
{
    /**
     * @var \MySQLi $_service connected mysqli service
     * @readwrite
     */
    protected $_service;

    /**
     * @var string $_host mysql host ip
     * @readwrite
     */
    protected $_host;

    /**
     * @var string $_username mysql username
     * @readwrite
     */
    protected $_username;

    /**
     * @var string $_password mysql user's password
     * @readwrite
     */
    protected $_password;

    /**
     * @var string $_schema mysql database name
     * @readwrite
     */
    protected $_schema;

    /**
     * @var string $_port mysql port
     * @readwrite
     */
    protected $_port = "3306";

    /**
     * @var string $_charset character set to use
     * @readwrite
     */
    protected $_charset = "utf8";

    /**
     * @var string $_engine mysql engine to use
     * @readwrite
     */
    protected $_engine = "InnoDB";

    /**
     * @var bool $_isConnected status of current mysql connection
     * @readwrite
     */
    protected $_isConnected = false;

    protected function _isValidService()
    {
        $isEmpty = empty($this->_service);
        $isInstance = $this->_service instanceof \MySQLi;

        if ($this->_isConnected && $isInstance && !$isEmpty) {
            return true;
        }

        return false;
    }

    public function connect()
    {
        if (!$this->_isValidService()) {
            $this->_service = new \MYSQLi(
                $this->_host,
                $this->_username,
                $this->_password,
                $this->_schema,
                $this->_port
            );

            if ($this->_service->connect_error) {
                throw new Exception\Service("Unable to connect to service");
            }

            $this->_isConnected = true;
        }

        return $this;
    }

    public function disconnect()
    {
        if ($this->_isValidService()) {
            $this->_isConnected = false;
            $this->_service->close();
        }

        return $this;
    }

    public function query()
    {
        return new Database\Query\Mysqli([
           "connector" => $this
        ]);
    }

    public function execute($sql)
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->query($sql);
    }

    public function escape($value)
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->real_escape_string($value);
    }

    public function getLastInsertId()
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->insert_id;
    }

    public function getAffectedRows()
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->affected_rows;
    }

    public function getLastError()
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->error;
    }

    public function sync($model)
    {
        $lines    = [];
        $indices  = [];
        $columns = $model->columns;
        $template = "CREATE TABLE `%s` (\n%s,\n%s\n) ENGINE=%s DEFAULT CHARSET=%s;";

        foreach ($columns as $column) {
            $raw = $column["raw"];
            $name = $column["name"];
            $type = $column["type"];
            $length = $column["length"];

            if ($column["primary"]) {
                $indices[] = "PRIMARY KEY (`{$name}`)";
            }

            if ($column["index"]) {
                $indices[] = "KEY `{$name}` (`{$name}`)";
            }

            switch ($type) {
                case "autonumber":
                    $lines[] = "`{$name}` int(11) NOT NULL AUTO_INCREMENT";
                    break;
                case "text":
                    if ($length !== null && $length <= 255) {
                        $lines[] = "`{$name}` varchar({$length}) DEFAULT NULL";
                    } else {
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
                    break;
            }
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

        if ($result === false) {
            $error = $this->lastError;
            throw new Exception\Sql("There was an error in the query: {$error}");
        }

        $result = $this->execute($sql);

        if ($result === false) {
            $error = $this->lastError;
            throw new Exception\Sql("There was an error in the query: {$error}");
        }

        return $this;
    }
}
