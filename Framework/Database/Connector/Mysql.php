<?php


namespace Framework\Database\Connector;

use Framework\Database as Database;
//use Framework\Database\Exception as Exception;
use Framework\Core\Exception as Exception;
use PDO;
use PDOException;

class Mysql extends Database\Connector
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
        return new Database\Query\Mysql([
            "connector" => $this
        ]);
    }
}