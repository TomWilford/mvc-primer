<?php
namespace Framework\Cache\Driver;

use Framework\Cache\Driver;
use Framework\Cache\Exception;
use Memcache;

class Memcached extends Driver
{
    /**
     * @var Memcache Memcache
     */
    protected $_service;

    /**
     * @var string $_host Memcached host ip
     * @readwrite
     */
    protected $_host = "127.0.0.1";

    /**
     * @var string $_port Memcached default port
     * @readwrite
     */
    protected $_port = "11211";

    /**
     * @var bool $_isConnected Memcached connection active?
     * @readwrite
     */
    protected $_isConnected = false;

    protected $_keyPrefix = "FRWK";

    protected function _isValidService()
    {
        $isEmpty    = empty($this->_service);
        $isInstance = $this->_service instanceof Memcache;

        if ($this->isConnected && $isInstance && !$isEmpty) {
            return true;
        }

        return false;
    }

    public function connect()
    {
        try {
            $this->_service = new Memcache();
            $this->_service->connect(
                $this->host,
                $this->port
            );
            $this->isConnected = true;
        }
        catch (\Exception $e) {
            throw new Exception\Service("Unable to connect to service" . $e->getMessage(), $e->getCode());
        }

        return $this;
    }

    public function disconnect()
    {
        if ($this->_isValidService()) {

            $this->_service->close();
            $this->isConnected = false;
        }

        return $this;
    }

    public function get($key, $default = null)
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service("Not connected to a valid service");
        }

        $flags = 2;

        $key = $this->_keyPrefix . $key;

        $value = $this->_service->get($key, $flags);

        if ($value) {
            return $value;
        }

        return $default;
    }

    public function set($key, $value, $duration = 120)
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service("Not connected to a valid service");
        }

        $key = $this->_keyPrefix . $key;
        $this->_service->set($key, $value, MEMCACHE_COMPRESSED, $duration);

        return $this;
    }

    public function erase($key)
    {
        if (!$this->_isValidService()) {
            throw new Exception\Service("Not connected to a valid service");
        }

        $key = $this->_keyPrefix . $key;
        $this->_service->delete($key);

        return $this;
    }
}
