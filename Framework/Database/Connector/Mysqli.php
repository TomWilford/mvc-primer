<?php

namespace Framework\Database\Connector;

use Framework\Database;
//use Framework\Database\Exception;
use Framework\Core\Exception;

/**
 * @deprecated
 */
class Mysqli extends Database\Connector
{
    protected $_service;

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
    protected $_port = "3306";

    /**
     * @var string
     * @readwrite
     */
    protected $_charset = "utf8";

    /**
     * @var string
     * @readwrite
     */
    protected $_engine = "InnoDB";

    /**
     * @var bool
     * @readwrite
     */
    protected $_isConnected = false;

    protected function _isValidService()
    {
        $isEmpty = empty($this->_service);
        $isInstance = $this->_service instanceof \MySQLi;

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
            $this->_service = new \MYSQLi(
                $this->_host,
                $this->_username,
                $this->_password,
                $this->_schema,
                $this->_port
            );

            if ($this->_service->connect_error)
            {
                throw new Exception\Service("Unable to connect to service");
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
        return new Database\Query\Mysqli(array(
           "connector" => $this
        ));
    }

    public function execute($sql)
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->query($sql);
    }

    public function escape($value)
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->real_escape_string($value);
    }

    public function getLastInsertId()
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->insert_id;
    }

    public function getAffectedRows()
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->affected_rows;
    }

    public function getLastError()
    {
        if (!$this->_isValidService())
        {
            throw new Exception\Service("Not connected to a valid service");
        }

        return $this->_service->error;
    }
}


