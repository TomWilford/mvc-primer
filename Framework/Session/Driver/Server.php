<?php
namespace Framework\Session\Driver;

use Framework\Session\Driver;

class Server extends Driver
{
    /**
     * @var string $_prefix
     * @readwrite
     */
    protected $_prefix = "app_";

    public function __construct($options = [])
    {
        parent::__construct($options);
        session_start();
    }

    public function get($key, $default = null)
    {
        if (isset($_SESSION[$this->prefix.$key])) {
            return $_SESSION[$this->prefix.$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        $_SESSION[$this->prefix.$key] = $value;
    }

    public function erase($key)
    {
        unset($_SESSION[$this->prefix.$key]);
        return $this;
    }
}
