<?php

/**
 * @deprecated
 * */

function autoload($class)
{
    $paths = explode(PATH_SEPARATOR, get_include_path());

    $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
    $file = strtolower(str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "\\")))."php";

    foreach ($paths as $path){
        $combined = $path.DIRECTORY_SEPARATOR.$file;

        if (file_exists($combined))
        {
            include($combined);
            return;
        }
    }
    throw new Exception("{$class} not found");
}

class Autoloader
{
    private static $autoloadResponseTime;

    public static function autoload($class)
    {
        autoload($class);
        self::setAutoloadResponseTime(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]);
    }

    public static function getAutoloadResponseTime()
    {
        return self::$autoloadResponseTime;
    }

    public static function setAutoloadResponseTime($autoloadResponseTime)
    {
        self::$autoloadResponseTime = $autoloadResponseTime;
    }
}
spl_autoload_register('autoload');
spl_autoload_register(['autoloader', 'autoload']);

// these can only be called within a class context...
// spl_autoload_register([$this, 'autoload']);
// spl_autoload_register(__CLASS__.'::load');