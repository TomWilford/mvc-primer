<?php
namespace Framework;

class RequestMethods
{
    private function __construct()
    {
        // nowt
    }

    private function __clone()
    {
        //nowt
    }

    public static function get($key, $default = "", $flag = FILTER_DEFAULT)
    {
        if (!empty($_GET[$key])) {
            return filter_input(INPUT_GET, $key, $flag);
        }

        return $default;
    }

    public static function post($key, $default = "", $flag = FILTER_DEFAULT)
    {
        if (!empty($_POST[$key])) {
            return filter_input(INPUT_POST, $key, $flag);
        }

        return $default;
    }

    public static function server($key, $default = "", $flag = FILTER_DEFAULT)
    {
        if (!empty($_SERVER[$key])) {
            return filter_input(INPUT_SERVER, $key, $flag);

        }

        return $default;
    }

    public static function cookie($key, $default = "", $flag = FILTER_DEFAULT)
    {
        if (!empty($_COOKIE[$key])) {
            return filter_input(INPUT_COOKIE, $key, $flag);
        }

        return $default;
    }
}
