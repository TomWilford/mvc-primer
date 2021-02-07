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
    /**
     * @var \MySQLi $_service connected mysqli service
     * @readwrite true
     */
    protected $_service;

    /**
     * @var string $_host mysql host ip
     * @readwrite true
     */
    protected $_host;

    /**
     * @var string $_username mysql username
     * @readwrite true
     */
    protected $_username;

    /**
     * @var string $_password mysql user's password
     * @readwrite true
     */
    protected $_password;

    /**
     * @var string $_schema mysql table name
     * @readwrite true
     */
    protected $_schema;

    /**
     * @var string $_port mysql port
     * @readwrite true
     */
    protected $_port = "3306";

    /**
     * @var string $_charset character set to use
     * @readwrite true
     */
    protected $_charset = "utf8";

    /**
     * @var string $_engine mysql engine to use
     * @readwrite true
     */
    protected $_engine = "InnoDB";

    /**
     * @var bool $_isConnected status of current mysql connection
     * @readwrite true
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

        return $this;
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
        return new Database\Query\Mysqli([
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


