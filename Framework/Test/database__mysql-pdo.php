<?php

require '../../vendor/autoload.php';

$options = [
    "type"    => "mysql_pdo",
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

        return ($database instanceof Framework\Database\Connector\MysqlPDO);
    },
    "Database\Connector\MysqlPDO initialises",
    "Database\Connector\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        return ($database instanceof Framework\Database\Connector\MysqlPDO);
    },
    "Database\Connector\MysqlPDO connects and returns self",
    "Database\Connector\MysqlPDO"
);

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
        catch (\Framework\Database\Exception\Service $e)
        {
            return ($database instanceof Framework\Database\Connector\MysqlPDO);
        }
        return false;
    },
    "Database\Connector\MysqlPDO disconnects and returns self",
    "Database\Connector\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        try
        {
            $database->execute("
                THIS IS NOT SQL
            ");
        }
        catch (\PDOException $e)
        {
            return (bool) $e->getMessage();
        }
        return false;
    },
    "Database\Connector\MysqlPDO returns last error",
    "Database\Connector\MysqlPDO"
);

var_dump(Framework\Test::run());