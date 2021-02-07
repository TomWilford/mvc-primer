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
    function ()
    {
        $database = new Framework\Database();
        return ($database instanceof Framework\Database);
    },
    "Database instantiates in an uninitialised state",
    "Database"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();

        return ($database instanceof Framework\Database\Connector\Mysqli);
    },
    "Database\Connector\Mysqli initialises",
    "Database\Connector\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        return ($database instanceof Framework\Database\Connector\Mysqli);
    },
    "Database\Connector\Mysqli connects and returns self",
    "Database\Connector\Mysqli"
);

/**
 *    ↓  ↓  ↓  This one will fail? ↓  ↓  ↓
 * */
Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();
        $database = $database->disconnect();

        try
        {
            $database->execute("SELECT 1");
        }
        catch (Framework\Core\Exception\Service $e)
        {
            return ($database instanceof Framework\Database\Connector\Mysqli);
        }
        return false;
    },
    "Database\Connector\Mysqli disconnects and returns self",
    "Database\Connector\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        return ($database->escape("foo'".'bar"') == "foo\\'bar\\\"");
    },
    "Database\Connector\Mysqli escapes values",
    "Database\Connector\Mysqli"
);

var_dump(Framework\Test::run());