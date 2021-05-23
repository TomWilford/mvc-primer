<?php

namespace Framework;

class Registry
{
    private static $_instances = [];

    private function __construct()
    {
        // nowt
    }

    private function __clone()
    {
        // nowt
    }

    public static function get($key, $default = null)
    {
        if (isset(self::$_instances[$key])) {
            return self::$_instances[$key];
        }

        return $default;
    }

    public static function set($key, $instance = null)
    {
        self::$_instances[$key] = $instance;
    }

    public static function erase($key)
    {
        unset(self::$_instances[$key]);
    }

    public static function getAll()
    {
        if (isset(self::$_instances)) {
            return self::$_instances;
        }

        return false;
    }

    public static function isClassRegistered($className)
    {
        foreach (self::getAll() as $key => $value) {
            if ($key == $className) {
                return true;
            }
        }

        return false;
    }
}
