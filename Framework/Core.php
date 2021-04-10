<?php

namespace Framework;

use Framework\Core\Exception;

class Core
{
    private static $_loaded = [];

    private static $_paths = [
        "/Application/Libraries",
        "/Application/Controllers",
        "/Application/Models",
        "/Application",
        ""
    ];

    public static function initialise()
    {
        if (!defined("APP_PATH"))
        {
            throw new Exception("APP_PATH not defined");
        }

        // fix extra backslashes in $_POST/$_GET

        $globals = ["_POST", "_GET", "_COOKIE", "_REQUEST", "_SESSION"];

        foreach ($globals as $global)
        {
            if (isset($GLOBALS[$global]))
            {
                $GLOBALS[$global] = self::_clean($GLOBALS[$global]);
            }
        }

        // start autoloading
        require '../vendor/autoload.php';
    }

    protected static function _clean($array)
    {
        if (is_array($array))
        {
            return array_map(__CLASS__."::_clean", $array);
        }
        return stripslashes($array);
    }
}