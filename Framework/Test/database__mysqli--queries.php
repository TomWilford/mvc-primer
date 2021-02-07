<?php

/**
 * @deprecated
 * */

require '../../vendor/autoload.php';

$options = [
    "type"    => "mysqli",
    "options" => [
        "host"     => "localhost",
        "username" => "prophpmvc",
        "password" => "prophpmvc",
        "schema"   => "prophpmvc"
    ]
];

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $database->execute("
            THIS IS NOT SQL
        ");

        return (bool) $database->lastError;
    },
    "Database\Connector\Mysqli returns last error",
    "Database\Connector\Mysqli"
);

var_dump(Framework\Test::run());